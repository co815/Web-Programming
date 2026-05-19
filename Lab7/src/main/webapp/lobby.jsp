<%@ page contentType="text/html;charset=UTF-8" %>
<%@ page import="com.lab7.model.Game" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lobby — X-0 Game</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
    <% if ("waiting_self".equals(request.getAttribute("lobbyState"))) { %>
    <meta http-equiv="refresh" content="3">
    <% } %>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>X-0 Lobby</h1>
        <span>Logged in as <strong><%= session.getAttribute("username") %></strong></span>
        <form method="post" action="${pageContext.request.contextPath}/logout" style="margin:0">
            <button type="submit" class="btn-secondary">Logout</button>
        </form>
    </div>

    <% String state = (String) request.getAttribute("lobbyState"); %>

    <% if ("create".equals(state)) { %>
    <div class="lobby-card">
        <p>No game in progress. Create one to start!</p>
        <form method="post" action="${pageContext.request.contextPath}/lobby">
            <input type="hidden" name="action" value="create">
            <button type="submit">Create Game</button>
        </form>
    </div>

    <% } else if ("waiting_self".equals(state)) { %>
    <div class="lobby-card">
        <p>Waiting for an opponent... (refreshes automatically)</p>
        <p>You are playing as <strong>X</strong></p>
        <form method="post" action="${pageContext.request.contextPath}/leave" style="margin-top:12px">
            <button type="submit" class="btn-secondary"
                    onclick="return confirm('Cancel the game?')">Cancel</button>
        </form>
    </div>

    <% } else if ("join".equals(state)) {
        Game g = (Game) request.getAttribute("game"); %>
    <div class="lobby-card">
        <p><strong><%= g.getPlayerXName() %></strong> is waiting for an opponent.</p>
        <form method="post" action="${pageContext.request.contextPath}/lobby">
            <input type="hidden" name="action" value="join">
            <button type="submit">Join Game</button>
        </form>
    </div>

    <% } else if ("full".equals(state)) {
        Game g = (Game) request.getAttribute("game"); %>
    <div class="lobby-card error-card">
        <p>A game between <strong><%= g.getPlayerXName() %></strong> and
           <strong><%= g.getPlayerOName() %></strong> is already in progress.</p>
        <p>Please wait for this game to finish before joining.</p>
        <button onclick="location.reload()" class="btn-secondary">Refresh</button>
    </div>
    <% } %>

</div>
</body>
</html>
