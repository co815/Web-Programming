package com.lab7.servlet;

import com.lab7.dao.GameDAO;
import com.lab7.model.Game;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/leave")
public class LeaveServlet extends HttpServlet {

    private final GameDAO gameDAO = new GameDAO();

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        Integer sessionGameId = (Integer) req.getSession().getAttribute("gameId");

        req.getSession().removeAttribute("gameId");

        if (sessionGameId != null) {
            try {
                Game game = gameDAO.findById(sessionGameId);
                if (game != null) {
                    if ("waiting".equals(game.getStatus())) {
                        gameDAO.deleteGame(sessionGameId);
                    } else if ("active".equals(game.getStatus())) {
                        int opponentId = (game.getPlayerXId() == userId)
                            ? game.getPlayerOId()
                            : game.getPlayerXId();
                        gameDAO.forfeit(sessionGameId, opponentId);
                    }
                }
            } catch (SQLException e) {
                throw new ServletException("Database error on leave", e);
            }
        }

        resp.sendRedirect(req.getContextPath() + "/lobby");
    }
}
