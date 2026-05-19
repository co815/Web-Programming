package com.lab7.servlet;

import com.lab7.dao.GameDAO;
import com.lab7.model.Game;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/game")
public class GameServlet extends HttpServlet {

    private final GameDAO gameDAO = new GameDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        Integer sessionGameId = (Integer) req.getSession().getAttribute("gameId");

        if (sessionGameId == null) {
            resp.sendRedirect(req.getContextPath() + "/lobby");
            return;
        }

        try {
            Game game = gameDAO.findById(sessionGameId);

            if (game == null) {
                req.getSession().removeAttribute("gameId");
                resp.sendRedirect(req.getContextPath() + "/lobby");
                return;
            }

            boolean isPlayerX = game.getPlayerXId() == userId;
            boolean isPlayerO = game.getPlayerOId() != null && game.getPlayerOId() == userId;

            if (!isPlayerX && !isPlayerO) {
                req.getSession().removeAttribute("gameId");
                resp.sendRedirect(req.getContextPath() + "/lobby");
                return;
            }

            req.setAttribute("playerSymbol", isPlayerX ? "X" : "O");
            req.setAttribute("game", game);
            req.getRequestDispatcher("/game.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException("Database error loading game", e);
        }
    }
}
