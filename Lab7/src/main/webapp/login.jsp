<%@ page contentType="text/html;charset=UTF-8" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — X-0 Game</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>
<div class="container">
    <h1>X-0 Game</h1>
    <h2>Login</h2>

    <% if (request.getAttribute("error") != null) { %>
        <p class="error"><%= request.getAttribute("error") %></p>
    <% } %>
    <% if ("true".equals(request.getParameter("registered"))) { %>
        <p class="success">Registration successful! Please log in.</p>
    <% } %>

    <form method="post" action="${pageContext.request.contextPath}/login">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<%= request.getAttribute("usernameValue") != null ? request.getAttribute("usernameValue") : "" %>"
                   autofocus required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>

    <p style="margin-top:16px">No account? <a href="${pageContext.request.contextPath}/register">Register</a></p>
</div>
</body>
</html>
