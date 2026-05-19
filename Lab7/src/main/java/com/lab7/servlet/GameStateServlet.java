package com.lab7.servlet;

import com.lab7.dao.GameDAO;
import com.lab7.model.Game;
import com.lab7.util.WinChecker;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/gamestate")
public class GameStateServlet extends HttpServlet {

    private final GameDAO gameDAO = new GameDAO();
    private final ObjectMapper mapper = new ObjectMapper();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        Integer sessionGameId = (Integer) req.getSession().getAttribute("gameId");

        resp.setContentType("application/json;charset=UTF-8");

        ObjectNode json = mapper.createObjectNode();

        if (sessionGameId == null) {
            json.put("status", "no_game");
            mapper.writeValue(resp.getWriter(), json);
            return;
        }

        try {
            Game game = gameDAO.findById(sessionGameId);

            if (game == null) {
                json.put("status", "no_game");
                mapper.writeValue(resp.getWriter(), json);
                return;
            }

            boolean isPlayerX = game.getPlayerXId() == userId;
            boolean isPlayerO = game.getPlayerOId() != null && game.getPlayerOId() == userId;
            String playerSymbol = isPlayerX ? "X" : (isPlayerO ? "O" : "");

            char turn = WinChecker.currentTurn(game.getBoard());

            json.put("gameId", game.getId());
            json.put("board", game.getBoard());
            json.put("status", game.getStatus());
            json.put("turn", String.valueOf(turn));
            json.put("playerSymbol", playerSymbol);
            json.put("playerXName", game.getPlayerXName());
            json.put("playerOName", game.getPlayerOName() != null ? game.getPlayerOName() : "");
            if (game.getWinnerName() != null) json.put("winnerName", game.getWinnerName());
            else json.putNull("winnerName");

            mapper.writeValue(resp.getWriter(), json);
        } catch (SQLException e) {
            throw new ServletException("Database error in gamestate", e);
        }
    }
}
