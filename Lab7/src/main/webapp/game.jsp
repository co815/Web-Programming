<%@ page contentType="text/html;charset=UTF-8" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>X-0 Game</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>X-0</h1>
        <span>You are <strong><%= request.getAttribute("playerSymbol") %></strong>
              &mdash; <strong><%= session.getAttribute("username") %></strong></span>
        <form method="post" action="${pageContext.request.contextPath}/leave" id="leaveForm" style="margin:0">
            <button type="button" class="btn-secondary" onclick="confirmLeave()">Leave</button>
        </form>
    </div>

    <div id="status-bar" class="status-bar">Connecting...</div>

    <div id="board" class="board">
        <% for (int i = 0; i < 9; i++) { %>
        <div class="cell" data-index="<%= i %>" onclick="makeMove(<%= i %>)"></div>
        <% } %>
    </div>

    <div id="overlay" class="overlay hidden">
        <div class="overlay-content">
            <h2 id="overlay-message"></h2>
            <form method="post" action="${pageContext.request.contextPath}/leave">
                <button type="submit">Return to Lobby</button>
            </form>
        </div>
    </div>
</div>

<script>
    const playerSymbol = '<%= request.getAttribute("playerSymbol") %>';
    const myUsername   = '<%= session.getAttribute("username") %>';
    const contextPath  = '${pageContext.request.contextPath}';
</script>
<script src="${pageContext.request.contextPath}/js/game.js"></script>
</body>
</html>
