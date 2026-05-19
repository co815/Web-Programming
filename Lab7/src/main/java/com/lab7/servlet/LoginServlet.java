package com.lab7.servlet;

import com.lab7.dao.UserDAO;
import com.lab7.model.User;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {

    private final UserDAO userDAO = new UserDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        HttpSession session = req.getSession(false);
        if (session != null && session.getAttribute("userId") != null) {
            resp.sendRedirect(req.getContextPath() + "/lobby");
            return;
        }
        req.getRequestDispatcher("/login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String username = req.getParameter("username");
        String password = req.getParameter("password");

        String error = validate(username, password);
        if (error != null) {
            req.setAttribute("error", error);
            req.setAttribute("usernameValue", username);
            req.getRequestDispatcher("/login.jsp").forward(req, resp);
            return;
        }

        try {
            User user = userDAO.authenticate(username, password);
            if (user == null) {
                req.setAttribute("error", "Invalid username or password.");
                req.setAttribute("usernameValue", username);
                req.getRequestDispatcher("/login.jsp").forward(req, resp);
                return;
            }
            HttpSession session = req.getSession(true);
            session.setAttribute("userId", user.getId());
            session.setAttribute("username", user.getUsername());
            resp.sendRedirect(req.getContextPath() + "/lobby");
        } catch (SQLException e) {
            throw new ServletException("Database error during login", e);
        }
    }

    private String validate(String username, String password) {
        if (username == null || username.isBlank()) return "Username is required.";
        if (password == null || password.isBlank()) return "Password is required.";
        return null;
    }
}
