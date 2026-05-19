<%@ page contentType="text/html;charset=UTF-8" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — X-0 Game</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>
<div class="container">
    <h1>X-0 Game</h1>
    <h2>Register</h2>

    <% if (request.getAttribute("error") != null) { %>
        <p class="error"><%= request.getAttribute("error") %></p>
    <% } %>

    <form method="post" action="${pageContext.request.contextPath}/register">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<%= request.getAttribute("usernameValue") != null ? request.getAttribute("usernameValue") : "" %>"
                   minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" autofocus required>
            <small>3–50 characters. Letters, digits, underscores only.</small>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="6" required>
            <small>At least 6 characters.</small>
        </div>
        <button type="submit">Register</button>
    </form>

    <p style="margin-top:16px">Already have an account? <a href="${pageContext.request.contextPath}/login">Login</a></p>
</div>
</body>
</html>
