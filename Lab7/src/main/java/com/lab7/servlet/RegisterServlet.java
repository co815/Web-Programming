package com.lab7.servlet;

import com.lab7.dao.UserDAO;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/register")
public class RegisterServlet extends HttpServlet {

    private final UserDAO userDAO = new UserDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/register.jsp").forward(req, resp);
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
            req.getRequestDispatcher("/register.jsp").forward(req, resp);
            return;
        }

        try {
            if (userDAO.findByUsername(username) != null) {
                req.setAttribute("error", "Username already taken.");
                req.setAttribute("usernameValue", username);
                req.getRequestDispatcher("/register.jsp").forward(req, resp);
                return;
            }
            userDAO.create(username, password);
            resp.sendRedirect(req.getContextPath() + "/login?registered=true");
        } catch (SQLException e) {
            throw new ServletException("Database error during registration", e);
        }
    }

    private String validate(String username, String password) {
        if (username == null || username.isBlank()) return "Username is required.";
        if (username.length() < 3) return "Username must be at least 3 characters.";
        if (username.length() > 50) return "Username must be at most 50 characters.";
        if (!username.matches("[a-zA-Z0-9_]+"))
            return "Username may only contain letters, digits, and underscores.";
        if (password == null || password.isBlank()) return "Password is required.";
        if (password.length() < 6) return "Password must be at least 6 characters.";
        return null;
    }
}
