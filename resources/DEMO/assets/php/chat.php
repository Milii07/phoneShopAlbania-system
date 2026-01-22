<div id="chatRobot">
    <img src="/new_project_bk/uploads/chat.robot/Chat.jpg" alt="Chat Robot" style="width:60px; height:60px; border-radius:50%; box-shadow:0 4px 15px rgba(0,0,0,0.3);">
</div>
<div id="chatWidget">
    <div id="chatHeader">
        Chat Auto Future Block
        <span id="chatClose" style="float:right; cursor:pointer;">✖</span>
    </div>
    <div id="chatBody"></div>
    <div style="padding:10px; border-top:1px solid #ddd; display:flex; gap:10px; align-items:center;">
        <input id="chatInput" type="text" placeholder="Shkruaj mesazhin..."
            style="flex:1; padding:10px 13px; font-size:14px; border-radius:23px; border:1px solid #ccc; outline:none;">
    </div>



    <div id="chatUserIcon">
        <img src="/new_project_bk/uploads/chat.robot/social.png" class="chat-icon" alt="Chat">
    </div>
    <div id="chatUserWidget">
        <div id="chatSidebar">
            <div id="chatSidebarHeader">Kontakti</div>
            <div id="chatContacts"></div>
        </div>
        <div id="chatArea">
            <div id="chatUserHeader">
                <span id="chatUserTitle">Biseda</span>
                <span id="chatUserClose" class="close-chat">✖</span>
            </div>
            <div id="chatUserBody"></div>
            <form id="chatUserForm">
                <input type="hidden" id="receiver_id" name="receiver_id">
                <input type="hidden" id="receiver_type" name="receiver_type">

                <div id="chatInputWrapper">
                    <span id="chatFileIcon" style="cursor:pointer; font-size:24px; margin-right:8px;">📷</span>
                    <input type="file" id="chatUserFile" name="file" style="display:none;">
                    <input type="text" id="chatUserInput" name="message" placeholder="Shkruaj mesazhin...">
                    <button type="submit">➤</button>
                </div>
            </form>
        </div>
    </div>