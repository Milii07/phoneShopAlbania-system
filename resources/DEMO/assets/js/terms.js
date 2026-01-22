let allMessages = {};

function initChatWidget() {
  $(function () {
    let USER_ID, IS_ADMIN, GUEST_ID, CURRENT_USER_TYPE, CURRENT_USER_ID;
    let selectedContact = null;
    let loadedMessageIds = new Set();
    let contactsData = {};
    let lastMessageDates = new Map();
    let isInitialLoad = true;
    let pusher = null;
    let myChannel = null;
    let typingTimeout = null;
    let isTyping = false;
    let messageQueue = [];
    let isProcessingQueue = false;
    let typingUsers = {};

    const TAB_ID = Math.random().toString(36).substring(7);
    let isTabActive = !document.hidden;
    let lastFetchTime = 0;
    const FETCH_COOLDOWN = 1000;

    const avatarColors = [
      "#FF6B6B",
      "#4ECDC4",
      "#45B7D1",
      "#FFA07A",
      "#98D8C8",
      "#F7DC6F",
      "#BB8FCE",
      "#85C1E2",
      "#F8B739",
      "#52B788",
      "#E76F51",
      "#F4A261",
      "#E9C46A",
      "#2A9D8F",
      "#264653",
      "#E63946",
      "#3dd406ff",
      "#2ebfc4ff",
      "#457B9D",
      "#1D3557",
    ];

    function getAvatarColor(name) {
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      const index = Math.abs(hash) % avatarColors.length;
      return avatarColors[index];
    }

    function getGuestSessionId() {
      let guestSessionId = localStorage.getItem("guest_session_id");

      if (!guestSessionId) {
        guestSessionId =
          "guest_" +
          Date.now() +
          "_" +
          Math.random().toString(36).substring(2, 15);
        localStorage.setItem("guest_session_id", guestSessionId);
        console.log("✓ Generated new guest session:", guestSessionId);
      } else {
        console.log("✓ Using existing guest session:", guestSessionId);
      }

      return guestSessionId;
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    function throttle(func, delay) {
      let lastCall = 0;
      return function (...args) {
        const now = Date.now();
        if (now - lastCall >= delay) {
          lastCall = now;
          return func.apply(this, args);
        }
      };
    }

    const guestSessionId = getGuestSessionId();

    $.ajax({
      url: "/new_project_bk/helper/send_message.php",
      method: "POST",
      data: {
        action: "get_user_info",
        guest_session_id: guestSessionId,
      },
      dataType: "json",
      async: false,
      success: function (resp) {
        if (resp.success) {
          USER_ID = resp.user_id;
          IS_ADMIN = resp.is_admin;
          GUEST_ID = resp.guest_id;
          CURRENT_USER_TYPE = resp.user_type;
          CURRENT_USER_ID = resp.current_id;
          console.log(
            "✓ User initialized:",
            CURRENT_USER_TYPE,
            CURRENT_USER_ID
          );
        }
      },
    });

    Pusher.logToConsole = false;
    pusher = new Pusher("d0652d5ed102a0e6056c", {
      cluster: "eu",
      useTLS: true,
    });

    const myChannelName = `chat-${CURRENT_USER_TYPE}-${CURRENT_USER_ID}`;
    myChannel = pusher.subscribe(myChannelName);

    myChannel.bind("pusher:subscription_succeeded", () => {
      console.log("✓ Connected to channel:", myChannelName);
      if ($("#chatUserWidget").is(":visible")) {
        updateOnlineStatus(true);
      }
    });

    function processMessageQueue() {
      if (isProcessingQueue || messageQueue.length === 0) return;

      isProcessingQueue = true;
      requestAnimationFrame(() => {
        const messages = [...messageQueue];
        messageQueue = [];

        messages.forEach((msg) => {
          processNewMessage(msg);
        });

        isProcessingQueue = false;
      });
    }

    myChannel.bind("new-message", function (msg) {
      if (!isTabActive && !$("#chatUserWidget").is(":visible")) {
        return;
      }

      messageQueue.push(msg);
      processMessageQueue();
    });

    function processNewMessage(msg) {
      if (loadedMessageIds.has(msg.id)) return;

      const isFromMe =
        String(msg.sender_id) === String(CURRENT_USER_ID) &&
        msg.sender_type === CURRENT_USER_TYPE;

      const contactKey = `${isFromMe ? msg.receiver_type : msg.sender_type}-${
        isFromMe ? msg.receiver_id : msg.sender_id
      }`;

      if (!contactsData[contactKey]) {
        let contactName = "";
        if (msg.sender_type === "admin" && !isFromMe) contactName = "Admin";
        else if (msg.receiver_type === "admin" && isFromMe)
          contactName = "Admin";
        else if (msg.sender_type === "guest")
          contactName = `Guest #${isFromMe ? msg.receiver_id : msg.sender_id}`;
        else if (msg.sender_type === "user")
          contactName = `User #${isFromMe ? msg.receiver_id : msg.sender_id}`;

        contactsData[contactKey] = {
          contact_id: isFromMe ? msg.receiver_id : msg.sender_id,
          contact_type: isFromMe ? msg.receiver_type : msg.sender_type,
          contact_name: contactName,
          unread_count: isFromMe ? 0 : 1,
          last_message: msg.message,
          last_message_time: msg.created_at,
          is_mine: isFromMe,
          is_read: msg.is_read || false,
          is_online: false,
          last_seen: null,
        };
      } else {
        if (!isFromMe) {
          if (
            !selectedContact ||
            String(selectedContact.contact_id) !== String(msg.sender_id) ||
            selectedContact.contact_type !== msg.sender_type
          ) {
            contactsData[contactKey].unread_count++;
          }
        }
        contactsData[contactKey].last_message = msg.message;
        contactsData[contactKey].last_message_time = msg.created_at;
        contactsData[contactKey].is_mine = isFromMe;
        contactsData[contactKey].is_read = msg.is_read || false;
      }

      debouncedRenderContacts();
      debouncedUpdateBadge();

      if (
        selectedContact &&
        ((String(msg.sender_id) === String(selectedContact.contact_id) &&
          msg.sender_type === selectedContact.contact_type) ||
          (String(msg.receiver_id) === String(selectedContact.contact_id) &&
            msg.receiver_type === selectedContact.contact_type))
      ) {
        hideTypingIndicator();
        appendMessage(msg);

        if (!isFromMe) {
          markMessagesAsRead(
            selectedContact.contact_id,
            selectedContact.contact_type
          );
        }
      }

      if (!isFromMe && isTabActive && Notification.permission === "granted") {
        new Notification("Mesazh i ri", {
          body: msg.message || "File",
          silent: true,
        });
      }
    }

    myChannel.bind(
      "message-delivered",
      throttle(function (data) {
        updateMessageStatus(data.message_id, "delivered");
      }, 500)
    );

    myChannel.bind(
      "message-read",
      throttle(function (data) {
        console.log("Message read event received:", data);
        updateMessageStatus(data.message_id, "read");

        if (data.sender_id && data.sender_type) {
          const contactKey = `${data.sender_type}-${data.sender_id}`;
          console.log("Updating contact:", contactKey);
          if (contactsData[contactKey]) {
            contactsData[contactKey].unread_count = 0;
            contactsData[contactKey].is_read = true;
            debouncedRenderContacts();
            debouncedUpdateBadge();
          }
        }
      }, 500)
    );

    myChannel.bind(
      "user-status-changed",
      throttle(function (data) {
        const contactKey = `${data.user_type}-${data.user_id}`;
        if (contactsData[contactKey]) {
          contactsData[contactKey].is_online = data.is_online;
          contactsData[contactKey].last_seen = data.last_seen;

          if (
            selectedContact &&
            String(selectedContact.contact_id) === String(data.user_id) &&
            selectedContact.contact_type === data.user_type
          ) {
            updateChatHeader();
          }
          debouncedRenderContacts();
        }
      }, 1000)
    );

    myChannel.bind("user-typing", function (data) {
      const contactKey = `${data.user_type}-${data.user_id}`;

      if (data.is_typing) {
        typingUsers[contactKey] = true;
      } else {
        delete typingUsers[contactKey];
      }

      debouncedRenderContacts();

      if (
        selectedContact &&
        String(selectedContact.contact_id) === String(data.user_id) &&
        selectedContact.contact_type === data.user_type
      ) {
        if (data.is_typing) {
          showTypingIndicator();
        } else {
          hideTypingIndicator();
        }
      }
    });

    function showTypingIndicator() {
      if ($("#typingIndicator").length === 0) {
        const typingHtml = `
          <div id="typingIndicator" class="chat-bubble their-message typing-indicator">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        `;
        $("#chatUserBody").append(typingHtml);
        $("#chatUserBody").scrollTop($("#chatUserBody")[0].scrollHeight);
      }
    }

    function hideTypingIndicator() {
      $("#typingIndicator").remove();
    }

    const sendTypingStatus = debounce(function (typing) {
      if (!selectedContact) return;

      $.post(
        "/new_project_bk/helper/send_message.php",
        {
          action: "typing_status",
          receiver_id: selectedContact.contact_id,
          receiver_type: selectedContact.contact_type,
          is_typing: typing ? 1 : 0,
          guest_session_id: guestSessionId,
        },
        null,
        "json"
      );
    }, 300);

    function updateMessageStatus(messageId, status) {
      requestAnimationFrame(() => {
        const $message = $(`.chat-bubble[data-msg-id="${messageId}"]`);
        if ($message.length) {
          const $statusIcon = $message.find(".msg-status");
          if (status === "delivered") {
            $statusIcon
              .removeClass("sent read")
              .addClass("delivered")
              .html("✓✓");
          } else if (status === "read") {
            $statusIcon
              .removeClass("sent delivered")
              .addClass("read")
              .html("✓✓");
          }
        }
      });
    }

    const updateOnlineStatus = throttle(function (isOnline) {
      $.post(
        "/new_project_bk/helper/send_message.php",
        {
          action: "update_online_status",
          is_online: isOnline ? 1 : 0,
          guest_session_id: guestSessionId,
        },
        null,
        "json"
      );
    }, 2000);

    function markMessagesAsRead(contactId, contactType) {
      console.log("Marking messages as read for:", contactType, contactId);
      $.post(
        "/new_project_bk/helper/send_message.php",
        {
          action: "mark_as_read",
          sender_id: contactId,
          sender_type: contactType,
          guest_session_id: guestSessionId,
        },
        function (resp) {
          console.log("Mark as read response:", resp);
          if (resp.success) {
            const contactKey = `${contactType}-${contactId}`;
            if (contactsData[contactKey]) {
              contactsData[contactKey].unread_count = 0;
              contactsData[contactKey].is_read = true;
              debouncedRenderContacts();
              debouncedUpdateBadge();
            }
          }
        },
        "json"
      );
    }

    function formatTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diff = now - date;
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));

      if (days === 0) {
        return date.toLocaleTimeString("sq-AL", {
          hour: "2-digit",
          minute: "2-digit",
        });
      }
      if (days === 1) return "Dje";
      if (days < 7) {
        return date.toLocaleDateString("sq-AL", { weekday: "short" });
      }
      return date.toLocaleDateString("sq-AL", {
        day: "2-digit",
        month: "2-digit",
      });
    }

    function getDateSeparator(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const msgDate = new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate()
      );
      const diff = today - msgDate;
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));

      if (days === 0) return "SOT";
      if (days === 1) return "DJE";
      return date.toLocaleDateString("sq-AL", {
        day: "2-digit",
        month: "long",
        year: "numeric",
      });
    }

    function needsDateSeparator(msgDate) {
      const dateKey = new Date(msgDate).toDateString();
      if (!lastMessageDates.has(dateKey)) {
        lastMessageDates.set(dateKey, true);
        return true;
      }
      return false;
    }

    function appendMessage(msg, scroll = true) {
      if (loadedMessageIds.has(msg.id)) return;

      requestAnimationFrame(() => {
        loadedMessageIds.add(msg.id);
        const body = $("#chatUserBody");

        if (needsDateSeparator(msg.created_at)) {
          const separator = $("<div>")
            .addClass("chat-date-separator")
            .text(getDateSeparator(msg.created_at));
          body.append(separator);
        }

        const isMine =
          String(msg.sender_id) === String(CURRENT_USER_ID) &&
          msg.sender_type === CURRENT_USER_TYPE;

        const bubble = $("<div>")
          .addClass("chat-bubble")
          .addClass(isMine ? "my-message" : "their-message")
          .attr("data-msg-id", msg.id);

        let content = "";
        if (msg.message) {
          const emojiOnly =
            /^[\p{Emoji}\s]+$/u.test(msg.message) && msg.message.length <= 6;
          if (emojiOnly) {
            content += `<div class="emoji-large">${$("<div>")
              .text(msg.message)
              .html()}</div>`;
          } else {
            content += `<div class="message-text">${$("<div>")
              .text(msg.message)
              .html()}</div>`;
          }
        }

        if (msg.file_path) {
          if (msg.file_path.match(/\.(jpeg|jpg|png|gif|webp)$/i)) {
            content += `<div class="chat-image-wrapper"><img src="/new_project_bk/uploads/chat_files/${msg.file_path}" class="chat-image" loading="lazy" /></div>`;
          } else {
            content += `<div class="chat-file-wrapper"><a href="/new_project_bk/uploads/chat_files/${msg.file_path}" class="chat-file-link" target="_blank"><span class="file-icon">📎</span> ${msg.file_path}</a></div>`;
          }
        }

        const time = new Date(msg.created_at).toLocaleTimeString("sq-AL", {
          hour: "2-digit",
          minute: "2-digit",
        });

        let statusIcon = "";
        if (isMine) {
          if (msg.is_read) {
            statusIcon = '<span class="msg-status read">✓✓</span>';
          } else if (msg.is_delivered) {
            statusIcon = '<span class="msg-status delivered">✓✓</span>';
          } else {
            statusIcon = '<span class="msg-status sent">✓</span>';
          }
        }

        content += `<div class="msg-time">${time} ${statusIcon}</div>`;
        bubble.html(content);
        body.append(bubble);

        if (scroll) body.scrollTop(body[0].scrollHeight);
      });
    }

    const debouncedUpdateBadge = debounce(function () {
      const totalUnreadContacts = Object.values(contactsData).filter(
        (c) => c.unread_count > 0
      ).length;

      let badge = $("#chatUserIcon .unread-badge");

      if (totalUnreadContacts > 0) {
        if (badge.length === 0) {
          badge = $('<span class="unread-badge"></span>');
          $("#chatUserIcon").append(badge);
        }
        badge.text(totalUnreadContacts > 99 ? "99+" : totalUnreadContacts);
      } else {
        badge.remove();
      }
    }, 200);

    function fetchContacts() {
      const now = Date.now();
      if (now - lastFetchTime < FETCH_COOLDOWN) {
        return;
      }
      lastFetchTime = now;

      $.post(
        "/new_project_bk/helper/send_message.php",
        {
          action: "fetch_contacts",
          guest_session_id: guestSessionId,
        },
        function (resp) {
          console.log("Fetch contacts response:", resp);
          console.log(
            "Number of contacts received:",
            resp.contacts ? resp.contacts.length : 0
          );
          if (resp.success) {
            const currentSelectedId = selectedContact
              ? `${selectedContact.contact_type}-${selectedContact.contact_id}`
              : null;

            contactsData = {};
            resp.contacts.forEach((c) => {
              const key = `${c.contact_type}-${c.contact_id}`;
              contactsData[key] = {
                contact_id: String(c.contact_id),
                contact_type: c.contact_type,
                contact_name: c.contact_name,
                unread_count: parseInt(c.unread_count) || 0,
                last_message: c.last_message || "",
                last_message_time: c.last_message_time || "",
                is_mine: c.is_mine || false,
                is_read: c.is_read || false,
                is_online: c.is_online || false,
                last_seen: c.last_seen || null,
              };

              if (currentSelectedId && key === currentSelectedId) {
                contactsData[key].unread_count = 0;
              }
            });
            console.log("About to call renderContacts()...");
            renderContacts();
            debouncedUpdateBadge();
          }
        },
        "json"
      );
    }

    const debouncedFetchContacts = debounce(fetchContacts, 500);

    const debouncedRenderContacts = debounce(function () {
      renderContacts();
    }, 100);

    function renderContacts() {
      requestAnimationFrame(() => {
        const $list = $("#chatContacts").empty();
        Object.values(contactsData)
          .sort(
            (a, b) =>
              new Date(b.last_message_time || 0) -
              new Date(a.last_message_time || 0)
          )
          .forEach((c) => {
            const contactKey = `${c.contact_type}-${c.contact_id}`;
            const isTyping = typingUsers[contactKey];

            const unread =
              c.unread_count > 0
                ? `<span class="unread-count">${c.unread_count}</span>`
                : "";
            const time = c.last_message_time
              ? formatTime(c.last_message_time)
              : "";

            let lastMsg;
            if (isTyping) {
              lastMsg = '<span class="typing-text">typing...</span>';
            } else {
              lastMsg = c.last_message || "Nuk ka mesazhe";
            }

            const onlineIndicator = c.is_online
              ? '<span class="online-dot"></span>'
              : "";

            const avatarColor = getAvatarColor(c.contact_name);

            let msgPreview = lastMsg;
            if (c.is_mine && !isTyping) {
              const statusIcon = c.is_read
                ? '<span class="msg-status read">✓✓</span>'
                : '<span class="msg-status delivered">✓✓</span>';
              msgPreview = `${statusIcon} ${lastMsg}`;
            }

            const $item = $(`
              <div class="contact-item" data-id="${c.contact_id}" data-type="${
              c.contact_type
            }">
                <div class="contact-avatar-wrapper">
                  <div class="contact-avatar" style="background-color: ${avatarColor}">${c.contact_name
              .charAt(0)
              .toUpperCase()}</div>
                  ${onlineIndicator}
                </div>
                <div class="contact-info">
                  <div class="contact-header">
                    <span class="contact-name">${c.contact_name}</span>
                    <span class="contact-time">${time}</span>
                  </div>
                  <div class="contact-preview">
                    <div class="contact-last-message">${msgPreview}</div>
                    ${unread}
                  </div>
                </div>
              </div>
            `);
            $item.on("click", () => selectContact(c));
            $list.append($item);
          });
      });
    }

    function updateChatHeader() {
      if (!selectedContact) return;

      const avatarColor = getAvatarColor(selectedContact.contact_name);

      const headerHtml = `
        <span class="back-button" id="backButton">←</span>
        <div class="chat-header-avatar" style="background-color: ${avatarColor}">${selectedContact.contact_name
        .charAt(0)
        .toUpperCase()}</div>
        <div class="chat-header-info">
          <div class="chat-header-name">${selectedContact.contact_name}</div>
          <div class="chat-header-status ${
            selectedContact.is_online ? "status-online" : ""
          }">
            ${
              selectedContact.is_online
                ? "online"
                : selectedContact.last_seen
                ? "last seen " + formatTime(selectedContact.last_seen)
                : "offline"
            }
          </div>
        </div>
      `;
      $("#chatUserHeader").html(headerHtml);

      $("#backButton").on("click", () => {
        $("#chatArea").removeClass("active");
        $("#chatSidebar").removeClass("hidden");
        selectedContact = null;
        hideTypingIndicator();
      });
    }

    function selectContact(contact) {
      selectedContact = contact;
      $("#receiver_id").val(contact.contact_id);
      $("#receiver_type").val(contact.contact_type);

      updateChatHeader();

      console.log("Selected contact:", selectedContact);

      $("#chatUserBody").empty();
      loadedMessageIds.clear();
      lastMessageDates.clear();
      isInitialLoad = true;

      const contactKey = `${contact.contact_type}-${contact.contact_id}`;
      contact.unread_count = 0;
      if (contactsData[contactKey]) {
        contactsData[contactKey].unread_count = 0;
      }
      renderContacts();
      debouncedUpdateBadge();

      $("#chatSidebar").addClass("hidden");
      $("#chatArea").addClass("active");

      fetchMessages();
      markMessagesAsRead(contact.contact_id, contact.contact_type);
    }

    function fetchMessages() {
      if (!selectedContact) return;

      $.post(
        "/new_project_bk/helper/send_message.php",
        {
          action: "fetch_messages",
          receiver_id: selectedContact.contact_id,
          receiver_type: selectedContact.contact_type,
          guest_session_id: guestSessionId,
        },
        function (resp) {
          if (!resp.success) return;

          const shouldScroll = isInitialLoad;
          resp.messages.forEach((m) => appendMessage(m, false));

          if (shouldScroll) {
            $("#chatUserBody").scrollTop($("#chatUserBody")[0].scrollHeight);
            isInitialLoad = false;
          }
        },
        "json"
      );
    }

    $("#chatUserInput").on("input", function () {
      const hasText = $(this).val().trim().length > 0;

      if (hasText && !isTyping) {
        isTyping = true;
        sendTypingStatus(true);
      }

      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(function () {
        if (isTyping) {
          isTyping = false;
          sendTypingStatus(false);
        }
      }, 2000);
    });

    $("#chatUserForm")
      .off("submit")
      .on("submit", function (e) {
        e.preventDefault();
        if (!selectedContact) return alert("Zgjidhni një kontakt!");

        const msg = $("#chatUserInput").val().trim();
        const fileInput = $("#chatUserFile")[0];
        if (!msg && (!fileInput || fileInput.files.length === 0)) return;

        clearTimeout(typingTimeout);
        if (isTyping) {
          isTyping = false;
          sendTypingStatus(false);
        }

        const fd = new FormData();
        fd.append("action", "send");
        fd.append("receiver_id", selectedContact.contact_id);
        fd.append("receiver_type", selectedContact.contact_type);
        fd.append("message", msg);
        fd.append("guest_session_id", guestSessionId);
        if (fileInput && fileInput.files.length > 0)
          fd.append("file", fileInput.files[0]);

        $("#chatUserInput").val("");
        $("#chatUserFile").val("");

        $.ajax({
          url: "/new_project_bk/helper/send_message.php",
          method: "POST",
          data: fd,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (resp) {
            if (resp.success) {
              const contactKey = `${selectedContact.contact_type}-${selectedContact.contact_id}`;
              if (contactsData[contactKey]) {
                contactsData[contactKey].last_message = msg;
                contactsData[contactKey].last_message_time = new Date()
                  .toISOString()
                  .slice(0, 19)
                  .replace("T", " ");
                contactsData[contactKey].is_mine = true;
                contactsData[contactKey].is_read = false;
                debouncedRenderContacts();
              }
            }
          },
        });
      });

    $("#chatFileIcon").on("click", () => $("#chatUserFile").click());

    $("#chatUserIcon").on("click", function (e) {
      e.stopPropagation();
      const isVisible = $("#chatUserWidget").is(":visible");
      $("#chatUserWidget").toggle();

      if (!isVisible) {
        fetchContacts();
        updateOnlineStatus(true);
      }
    });

    $("#chatUserClose").on("click", function (e) {
      e.stopPropagation();
      $("#chatUserWidget").hide();
      updateOnlineStatus(false);
      hideTypingIndicator();
    });

    $(document).on("click", function (e) {
      const $widget = $("#chatUserWidget");
      const $icon = $("#chatUserIcon");

      if (
        $widget.is(":visible") &&
        !$widget.is(e.target) &&
        $widget.has(e.target).length === 0 &&
        !$icon.is(e.target) &&
        $icon.has(e.target).length === 0
      ) {
        $widget.hide();
        updateOnlineStatus(false);
        hideTypingIndicator();
      }
    });

    $("#chatUserWidget").on("click", function (e) {
      e.stopPropagation();
    });

    $(window).on("beforeunload", function () {
      updateOnlineStatus(false);
      if (myChannel) {
        myChannel.unbind_all();
        pusher.unsubscribe(myChannelName);
      }
      if (pusher) {
        pusher.disconnect();
      }
    });

    document.addEventListener("visibilitychange", function () {
      isTabActive = !document.hidden;

      if (isTabActive) {
        if ($("#chatUserWidget").is(":visible")) {
          updateOnlineStatus(true);
          fetchContacts();
        }
      } else {
        if ($("#chatUserWidget").is(":visible")) {
          updateOnlineStatus(false);
        }
      }
    });

    if (
      Notification.permission !== "granted" &&
      Notification.permission !== "denied"
    ) {
      Notification.requestPermission();
    }

    $(document).ready(() => {
      $("#chatUserWidget").hide();
      fetchContacts();
      debouncedUpdateBadge();
    });
  });
}

function initChatRobot() {
  const chatRobot = document.getElementById("chatRobot");
  const chatWidget = document.getElementById("chatWidget");
  const chatClose = document.getElementById("chatClose");
  const chatBody = document.getElementById("chatBody");
  const chatInput = document.getElementById("chatInput");
  const chatSendBtn = document.getElementById("chatSendBtn");

  let lastBotResponse = null;

  const appendMessage = (msg, sender = "bot") => {
    const div = document.createElement("div");
    div.classList.add(
      "chat-message",
      sender === "bot" ? "chat-bot" : "chat-user"
    );
    div.innerHTML = msg;
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
  };

  const sendMessage = async (payload, autoAppend = true) => {
    try {
      const res = await fetch("/new_project_bk/helper/chatHandler.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      lastBotResponse = data;
      if (autoAppend && data.reply) appendMessage(data.reply, "bot");
      return data;
    } catch (err) {
      console.error(err);
      appendMessage("Gabim gjatë komunikimit me serverin", "bot");
    }
  };

  const greetOnOpen = async () =>
    await sendMessage({
      event: "open",
    });

  const showReservationCalendar = (startDate) => {
    if (document.querySelector(".chat-calendar")) return;

    const calendarDiv = document.createElement("div");
    calendarDiv.classList.add("chat-calendar");
    calendarDiv.innerHTML = `
        <div class="calendar-box">
            <p><strong>Zgjidh datat e rezervimit:</strong></p>
            <label>Data e nisjes:</label>
            <input type="date" id="start-date" min="${startDate}" value="${startDate}">
            <label>Data e mbarimit:</label>
            <input type="date" id="end-date" min="${startDate}">
            <button id="confirm-reservation">Rezervo</button>
        </div>
    `;
    chatBody.appendChild(calendarDiv);
    chatBody.scrollTop = chatBody.scrollHeight;

    const oldBtn = document.getElementById("confirm-reservation");
    if (oldBtn) oldBtn.replaceWith(oldBtn.cloneNode(true));

    document
      .getElementById("confirm-reservation")
      .addEventListener("click", async () => {
        const start = document.getElementById("start-date").value;
        const end = document.getElementById("end-date").value;

        if (!start || !end) {
          alert("Zgjidh datat e rezervimit!");
          return;
        }

        appendMessage(`Rezervimi nga ${start} deri më ${end}.`, "user");

        const res = await sendMessage(
          {
            action: "reserve",
            start_date: start,
            end_date: end,
          },
          false
        );
        if (res.reply) appendMessage(res.reply, "bot");

        document.querySelector(".chat-calendar")?.remove();
      });
  };

  const handleUserMessage = async (msg) => {
    appendMessage(msg, "user");

    if (
      lastBotResponse &&
      lastBotResponse.expected_confirmations &&
      lastBotResponse.next_available_date
    ) {
      const positiveWords = lastBotResponse.expected_confirmations.map((w) =>
        w.toLowerCase()
      );
      if (positiveWords.some((w) => msg.toLowerCase().includes(w))) {
        showReservationCalendar(lastBotResponse.next_available_date);
        return;
      }
    }

    await sendMessage({
      message: msg,
    });
  };

  chatInput.addEventListener("keypress", async (e) => {
    if (e.key === "Enter" && chatInput.value.trim() !== "") {
      const msg = chatInput.value.trim();
      chatInput.value = "";
      await handleUserMessage(msg);
    }
  });

  chatSendBtn?.addEventListener("click", async () => {
    const msg = chatInput.value.trim();
    if (!msg) return;
    chatInput.value = "";
    await handleUserMessage(msg);
  });

  chatRobot.addEventListener("click", async () => {
    if (chatWidget.style.display !== "flex") {
      chatWidget.style.display = "flex";
      chatInput.focus();
      await greetOnOpen();
    } else {
      chatWidget.style.display = "none";
    }
  });

  chatClose.addEventListener(
    "click",
    () => (chatWidget.style.display = "none")
  );
}

function initfooter() {
  $(document).ready(function () {
    let today = new Date().toISOString().split("T")[0];
    $('[name="pickup_date"]').val(today);

    let tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    let tomorrowDate = tomorrow.toISOString().split("T")[0];
    $('[name="dropoff_date"]').val(tomorrowDate);

    $(".footer-section .services a").on("click", function (e) {
      e.preventDefault();

      let href = $(this).attr("href");

      let serviceType = "all";
      if (href.includes("#nightparties")) {
        serviceType = "Night Parties";
      } else if (href.includes("#weddings")) {
        serviceType = "Weddings";
      } else if (href.includes("#airport")) {
        serviceType = "Airport Transfers";
      } else if (href.includes("#casinos")) {
        serviceType = "Casinos";
      } else if (href.includes("#birthdays")) {
        serviceType = "Birthdays";
      } else if (href.includes("#business")) {
        serviceType = "Business";
      }

      $('[name="service_type"]').val(serviceType);

      let section = $(".booking-section");
      if (section == undefined || section.length == 0) {
        section = $(".car-grid");
      }

      $("html, body").animate(
        {
          scrollTop: section?.offset().top - 100,
        },
        800
      );

      setTimeout(function () {
        $("#bookingForm").submit();
      }, 500);
    });

    $("#bookingForm").on("submit", function (e) {
      e.preventDefault();

      let pickup_date = $('[name="pickup_date"]').val();
      let pickup_time = $('[name="pickup_time"]').val();
      let dropoff_date = $('[name="dropoff_date"]').val();
      let dropoff_time = $('[name="dropoff_time"]').val();
      let service_type = $('[name="service_type"]').val();

      if (
        !pickup_date ||
        !pickup_time ||
        !dropoff_date ||
        !dropoff_time ||
        !service_type
      ) {
        alert("Plotëso të gjitha fushat!");
        return;
      }

      let loadingHtml =
        '<div style="text-align: center; padding: 15px; display: block;"><div class="text-primary" role="status"><span class="visually-hidden"></span></div><p class="mt-3"></p></div>';

      if ($(".car-grid").length > 0) {
        $(".car-grid").html(loadingHtml);
      }

      $.ajax({
        url: "/new_project_bk/helper/reservations.php",
        type: "POST",
        data: {
          action: "search_cars",
          pickup_date: pickup_date,
          pickup_time: pickup_time,
          dropoff_date: dropoff_date,
          dropoff_time: dropoff_time,
          service_type: service_type,
        },
        dataType: "json",
        success: function (response) {
          console.log("Response:", response);

          if (response.error) {
            alert(response.error);
            return;
          }

          let cars = response;
          let html = "";

          let serviceLabels = {
            all: "Të gjitha shërbimet",
            Weddings: "Dasmë",
            "Night Parties": "Night Party",
            "Airport Transfers": "Transfer Aeroporti",
            Casinos: "Kazino",
            Birthdays: "Ditëlindje",
            Business: "Biznes",
          };

          if (cars.length > 0) {
            let serviceLabel = serviceLabels[service_type] || service_type;
            html +=
              '<h3 style="margin-bottom: 20px; text-align: center;">Makinat e lira për: <strong>' +
              serviceLabel +
              "</strong> (" +
              cars.length +
              ' makina)</h3><div class="car-grid">';

            cars.forEach(function (car) {
              let modalId = "carModalAvailable" + car.id;
              let rating = car.rating || "4.5";
              let seats = car.seats || "5";
              let transmission = car.transmission || "Manual";
              let type = car.type || "Sedan";
              let carServiceType = car.service_type || "N/A";

              html += `
        <div class="car-card">
            <img src="${car.image}" alt="${
                car.model
              }" class="car-image" style="cursor: pointer;" onclick="openCarModal('${modalId}')">
            <div class="car-rating">⭐ ${rating}</div>
            <div class="car-content">
                <h3 class="car-name">${car.model}</h3>
                <div class="car-specs">
                    <span class="spec">👥 ${seats} vende</span>
                    <span class="spec">⚙️ ${transmission}</span>
                    <span class="spec">🚗 ${type}</span>
                </div>
                <div class="car-specs" style="margin-top: 8px;">
                    <span class="spec" style="background: #e3f2fd; color: #1976d2; font-weight: 600;"> ${
                      serviceLabels[carServiceType] || carServiceType
                    }</span>
                </div>
                <div class="car-footer">
                    <div class="car-price-clean"> <span class="price-value">${
                      car.price_per_day
                    }€</span> <span class="price-label">/ditë</span></div>
                    <button class="btn btn-success btn-sm reserve-btn" 
                        data-car-id="${car.id}"
                        data-car-name="${car.model}"
                        data-pickup-date="${pickup_date}"
                        data-pickup-time="${pickup_time}"
                        data-dropoff-date="${dropoff_date}"
                        data-dropoff-time="${dropoff_time}"
                        data-service-type="${carServiceType}">
                        Rezervo
                    </button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${car.model}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <img src="${
                          car.image
                        }" class="img-fluid rounded mb-3" style="max-height:400px; object-fit:cover;">
                        <p class="text-muted mb-1">${type} | ${transmission}</p>
                        <p class="fs-5 fw-bold " style="2c599d"> ${
                          car.price_per_day
                        } €/ditë</p>
                        <p class="text-muted small">⭐ ${rating} | 💺 ${seats} vende |  ${
                serviceLabels[carServiceType] || carServiceType
              }</p>
                        <hr class="my-3">
                        <p>Ky model makine ofron një eksperiencë të jashtëzakonshme udhëtimi. Sediljet janë të rehatshme dhe të rregullueshme sipas preferencave.</p>
                        <p>Pajisjet teknologjike, përfshirë navigacionin, sistemin e ndihmës për parkim dhe asistencën e vozitjes, garantojnë një eksperiencë të sigurt.</p>
                        <p>Pajisjet moderne të sigurisë, si airbag-et, ABS, kontrolli i stabilitetit dhe sistemi i paralajmërimit për rrezik.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success reserve-btn-modal"
                            data-car-id="${car.id}"
                            data-car-name="${car.model}"
                            data-pickup-date="${pickup_date}"
                            data-pickup-time="${pickup_time}"
                            data-dropoff-date="${dropoff_date}"
                            data-dropoff-time="${dropoff_time}"
                            data-service-type="${carServiceType}">
                            Rezervo Këtë Makinë
                        </button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Mbyll</button>
                    </div>
                </div>
            </div>
        </div>
        `;
            });

            html += "</div>";
          } else {
            let serviceLabel = serviceLabels[service_type] || service_type;
            html =
              '<div style="text-align: center; padding: 40px;"><h3>Nuk ka makina të lira për: <strong>' +
              serviceLabel +
              "</strong></h3><p>Ju lutem zgjidhni një opsion tjetër ose data të tjera.</p></div>";
          }

          if ($("#availableCars").length > 0) {
            $("#availableCars").html(html);
          } else if ($(".car-grid").length > 0) {
            $(".car-grid").replaceWith(html);
          } else {
            $("#bookingForm").after(
              '<div id="availableCars">' + html + "</div>"
            );
          }

          $(".dashboard-cards").hide();
          $("#salesChart").hide();

          attachReserveButtonHandlers();

          if ($("#availableCars").length > 0) {
            $("html, body").animate(
              {
                scrollTop: $("#availableCars").offset().top - 100,
              },
              500
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
          alert("Ka ndodhur një gabim gjatë kërkimit të makinave!");
        },
      });
    });

    window.openCarModal = function (modalId) {
      $("#" + modalId).modal("show");
    };

    function attachReserveButtonHandlers() {
      $(".reserve-btn, .reserve-btn-modal")
        .off("click")
        .on("click", function () {
          let carId = $(this).data("car-id");
          let carName = $(this).data("car-name");
          let pickupDate = $(this).data("pickup-date");
          let pickupTime = $(this).data("pickup-time");
          let dropoffDate = $(this).data("dropoff-date");
          let dropoffTime = $(this).data("dropoff-time");
          let serviceType = $(this).data("service-type");

          console.log("Reserve clicked:", {
            carId,
            carName,
            pickupDate,
            pickupTime,
            dropoffDate,
            dropoffTime,
            serviceType,
          });

          $(".modal").modal("hide");

          setTimeout(function () {
            $('#addReservationModal select[name="car_id"]').val(carId);
            $('#addReservationModal input[name="start_date"]').val(pickupDate);
            $('#addReservationModal input[name="time"]').val(pickupTime);
            $('#addReservationModal input[name="end_date"]').val(dropoffDate);

            if ($('#addReservationModal input[name="service_type"]').length) {
              $('#addReservationModal input[name="service_type"]').val(
                serviceType
              );
            }

            console.log(
              "Opening reservation modal with service type:",
              serviceType
            );

            $("#addReservationModal").modal("show");
          }, 500);
        });
    }

    let currentReservationModalId = null;

    $(document).on("click", '[data-bs-target="#addClientModal"]', function () {
      currentReservationModalId = $(this).data("current-reserve-modal");
      console.log("Opening client modal from:", currentReservationModalId);

      if (currentReservationModalId) {
        $("#" + currentReservationModalId).modal("hide");
      }
    });

    $("#addClientForm").on("submit", function (e) {
      e.preventDefault();

      let formData = $(this).serialize();
      let submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop("disabled", true);

      $.ajax({
        url: "/new_project_bk/helper/save_client_ajax.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          submitBtn.prop("disabled", false);

          if (response.success) {
            $("#clientFormMessage").html(
              '<div class="alert alert-success">Klienti u shtua me sukses!</div>'
            );

            let newOption = new Option(
              response.client_name,
              response.client_id,
              true,
              true
            );

            $("#clientSelect").append(newOption).trigger("change");
            $('#addReservationModal select[name="client_id"]')
              .append(newOption.cloneNode(true))
              .val(response.client_id);

            setTimeout(function () {
              $("#addClientModal").modal("hide");

              if (currentReservationModalId) {
                setTimeout(function () {
                  $("#" + currentReservationModalId).modal("show");
                  currentReservationModalId = null;
                }, 300);
              }

              $("#addClientForm")[0].reset();
              $("#clientFormMessage").html("");
            }, 1000);
          } else {
            $("#clientFormMessage").html(
              '<div class="alert alert-danger">' +
                (response.message ||
                  response.error ||
                  "Gabim gjatë shtimit të klientit!") +
                "</div>"
            );
          }
        },
        error: function (xhr, status, error) {
          submitBtn.prop("disabled", false);
          console.error("Error:", xhr.responseText);
          $("#clientFormMessage").html(
            '<div class="alert alert-danger">Ka ndodhur një gabim në server!</div>'
          );
        },
      });
    });

    $("#addClientModal").on("hidden.bs.modal", function () {
      $("#clientFormMessage").html("");
    });
  });
  const hamburger = document.querySelector(".hamburger");
  const navLinks = document.querySelector(".nav-links");

  hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("active");
    hamburger.classList.toggle("toggle");
  });
}

function initRegionDropdown() {
  const regionDropdown = document.getElementById("region-dropdown");
  const changeBtn = document.getElementById("change-region");
  const currentRegionText = document.getElementById("current-region");

  changeBtn.addEventListener("click", () => {
    regionDropdown.style.display =
      regionDropdown.style.display === "block" ? "none" : "block";
  });

  document.querySelectorAll("#region-dropdown li").forEach((li) => {
    li.addEventListener("click", () => {
      currentRegionText.textContent = li.dataset.value;
      regionDropdown.style.display = "none";
    });
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".region-block-container")) {
      regionDropdown.style.display = "none";
    }
  });
}

function receiveMessage(msg) {
  let contactId = msg.sender_id;

  if (!allMessages[contactId]) {
    allMessages[contactId] = [];
  }

  allMessages[contactId].push(msg);
}

function initSearch() {
  document.getElementById("chatSearch").addEventListener("input", function () {
    let filter = this.value.toLowerCase();

    document.querySelectorAll("#chatContacts .contact-item").forEach((item) => {
      let contactId = item.dataset.contactId;
      let haystack = item.textContent.toLowerCase();

      if (allMessages[contactId]) {
        allMessages[contactId].forEach((m) => {
          haystack += " " + m.message.toLowerCase();
        });
      }

      item.style.display = haystack.includes(filter) ? "flex" : "none";
    });
  });
}
