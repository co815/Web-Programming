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

@WebServlet("/move")
public class MoveServlet extends HttpServlet {

    private final GameDAO gameDAO = new GameDAO();
    private final ObjectMapper mapper = new ObjectMapper();

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int userId = (int) req.getSession().getAttribute("userId");
        Integer sessionGameId = (Integer) req.getSession().getAttribute("gameId");

        resp.setContentType("application/json;charset=UTF-8");

        if (sessionGameId == null) {
            resp.setStatus(400);
            mapper.writeValue(resp.getWriter(), errorJson("Not in a game"));
            return;
        }

        int position;
        try {
            position = Integer.parseInt(req.getParameter("position"));
        } catch (NumberFormatException e) {
            resp.setStatus(400);
            mapper.writeValue(resp.getWriter(), errorJson("Invalid position"));
            return;
        }

        if (position < 0 || position > 8) {
            resp.setStatus(400);
            mapper.writeValue(resp.getWriter(), errorJson("Position must be 0-8"));
            return;
        }

        try {
            Game game = gameDAO.findById(sessionGameId);

            if (game == null || !"active".equals(game.getStatus())) {
                resp.setStatus(400);
                mapper.writeValue(resp.getWriter(), errorJson("Game not active"));
                return;
            }

            boolean isPlayerX = game.getPlayerXId() == userId;
            boolean isPlayerO = game.getPlayerOId() != null && game.getPlayerOId() == userId;

            if (!isPlayerX && !isPlayerO) {
                resp.setStatus(403);
                mapper.writeValue(resp.getWriter(), errorJson("Not your game"));
                return;
            }

            char mySymbol = isPlayerX ? 'X' : 'O';
            char turn = WinChecker.currentTurn(game.getBoard());

            if (turn != mySymbol) {
                resp.setStatus(403);
                mapper.writeValue(resp.getWriter(), errorJson("Not your turn"));
                return;
            }

            if (game.getBoard().charAt(position) != '-') {
                resp.setStatus(400);
                mapper.writeValue(resp.getWriter(), errorJson("Cell already occupied"));
                return;
            }

            char[] boardArr = game.getBoard().toCharArray();
            boardArr[position] = mySymbol;
            String newBoard = new String(boardArr);

            char result = WinChecker.checkResult(newBoard);
            String newStatus;
            Integer winnerId = null;

            if (result == mySymbol) {
                newStatus = "finished";
                winnerId = userId;
            } else if (result == 'D') {
                newStatus = "finished";
            } else {
                newStatus = "active";
            }

            gameDAO.updateBoard(sessionGameId, newBoard, newStatus, winnerId);
            Game updated = gameDAO.findById(sessionGameId);
            mapper.writeValue(resp.getWriter(), buildStateJson(updated, userId));
        } catch (SQLException e) {
            throw new ServletException("Database error in move", e);
        }
    }

    private ObjectNode buildStateJson(Game game, int userId) {
        ObjectNode json = mapper.createObjectNode();
        boolean isPlayerX = game.getPlayerXId() == userId;
        boolean isPlayerO = game.getPlayerOId() != null && game.getPlayerOId() == userId;
        json.put("gameId", game.getId());
        json.put("board", game.getBoard());
        json.put("status", game.getStatus());
        json.put("turn", String.valueOf(WinChecker.currentTurn(game.getBoard())));
        json.put("playerSymbol", isPlayerX ? "X" : (isPlayerO ? "O" : ""));
        json.put("playerXName", game.getPlayerXName());
        json.put("playerOName", game.getPlayerOName() != null ? game.getPlayerOName() : "");
        if (game.getWinnerName() != null) json.put("winnerName", game.getWinnerName());
        else json.putNull("winnerName");
        return json;
    }

    private ObjectNode errorJson(String message) {
        ObjectNode json = mapper.createObjectNode();
        json.put("error", message);
        return json;
    }
}
