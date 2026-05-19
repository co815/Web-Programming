package com.lab7.servlet;

import com.lab7.dao.GameDAO;
import com.lab7.model.Game;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/lobby")
public class LobbyServlet extends HttpServlet {

    private final GameDAO gameDAO = new GameDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        Integer sessionGameId = (Integer) req.getSession().getAttribute("gameId");

        try {
            if (sessionGameId != null) {
                Game game = gameDAO.findById(sessionGameId);
                if (game != null && "active".equals(game.getStatus())) {
                    resp.sendRedirect(req.getContextPath() + "/game");
                    return;
                } else if (game != null && "waiting".equals(game.getStatus())) {
                    req.setAttribute("game", game);
                    req.setAttribute("lobbyState", "waiting_self");
                    req.getRequestDispatcher("/lobby.jsp").forward(req, resp);
                    return;
                } else {
                    req.getSession().removeAttribute("gameId");
                }
            }

            Game game = gameDAO.findActive();

            if (game == null) {
                req.setAttribute("lobbyState", "create");
            } else if ("active".equals(game.getStatus())) {
                req.setAttribute("game", game);
                req.setAttribute("lobbyState", "full");
            } else {
                if (game.getPlayerXId() == userId) {
                    req.getSession().setAttribute("gameId", game.getId());
                    req.setAttribute("game", game);
                    req.setAttribute("lobbyState", "waiting_self");
                } else {
                    req.setAttribute("game", game);
                    req.setAttribute("lobbyState", "join");
                }
            }

            req.getRequestDispatcher("/lobby.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException("Database error in lobby", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        String action = req.getParameter("action");

        try {
            if ("create".equals(action)) {
                Game game = gameDAO.createGame(userId);
                req.getSession().setAttribute("gameId", game.getId());
            } else if ("join".equals(action)) {
                Game existing = gameDAO.findActive();
                if (existing != null && "waiting".equals(existing.getStatus())
                        && existing.getPlayerXId() != userId) {
                    gameDAO.joinGame(existing.getId(), userId);
                    req.getSession().setAttribute("gameId", existing.getId());
                }
            }
            resp.sendRedirect(req.getContextPath() + "/lobby");
        } catch (SQLException e) {
            throw new ServletException("Database error in lobby action", e);
        }
    }
}
