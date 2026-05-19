package com.lab7.dao;

import com.lab7.model.Game;
import com.lab7.util.DBConnection;

import java.sql.*;

public class GameDAO {

    private static final String SELECT =
        "SELECT g.id, g.player_x_id, ux.username AS x_name, " +
        "g.player_o_id, uo.username AS o_name, " +
        "g.board, g.status, g.winner_id, uw.username AS w_name " +
        "FROM game g " +
        "JOIN users ux ON ux.id = g.player_x_id " +
        "LEFT JOIN users uo ON uo.id = g.player_o_id " +
        "LEFT JOIN users uw ON uw.id = g.winner_id";

    private Game mapRow(ResultSet rs) throws SQLException {
        Game g = new Game();
        g.setId(rs.getInt("id"));
        g.setPlayerXId(rs.getInt("player_x_id"));
        g.setPlayerXName(rs.getString("x_name"));
        int oId = rs.getInt("player_o_id");
        g.setPlayerOId(rs.wasNull() ? null : oId);
        g.setPlayerOName(rs.getString("o_name"));
        g.setBoard(rs.getString("board"));
        g.setStatus(rs.getString("status"));
        int wId = rs.getInt("winner_id");
        g.setWinnerId(rs.wasNull() ? null : wId);
        g.setWinnerName(rs.getString("w_name"));
        return g;
    }

    public Game findActive() throws SQLException {
        String sql = SELECT + " WHERE g.status IN ('waiting','active') ORDER BY g.created_at DESC LIMIT 1";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            return rs.next() ? mapRow(rs) : null;
        }
    }

    public Game findById(int id) throws SQLException {
        String sql = SELECT + " WHERE g.id = ?";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? mapRow(rs) : null;
            }
        }
    }

    public Game createGame(int playerXId) throws SQLException {
        try (Connection conn = DBConnection.getConnection()) {
            try (PreparedStatement del = conn.prepareStatement("DELETE FROM game WHERE status = 'finished'")) {
                del.executeUpdate();
            }

            String sql = "INSERT INTO game (player_x_id) VALUES (?)";
            try (PreparedStatement ps = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
                ps.setInt(1, playerXId);
                ps.executeUpdate();
                try (ResultSet keys = ps.getGeneratedKeys()) {
                    keys.next();
                    return findById(keys.getInt(1));
                }
            }
        }
    }

    public Game joinGame(int gameId, int playerOId) throws SQLException {
        String sql = "UPDATE game SET player_o_id = ?, status = 'active' WHERE id = ? AND status = 'waiting'";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, playerOId);
            ps.setInt(2, gameId);
            if (ps.executeUpdate() == 0) {
                throw new SQLException("Join failed: game is no longer available");
            }
            return findById(gameId);
        }
    }

    public void updateBoard(int gameId, String board, String status, Integer winnerId) throws SQLException {
        String sql = "UPDATE game SET board = ?, status = ?, winner_id = ? WHERE id = ?";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, board);
            ps.setString(2, status);
            if (winnerId != null) ps.setInt(3, winnerId);
            else ps.setNull(3, Types.INTEGER);
            ps.setInt(4, gameId);
            ps.executeUpdate();
        }
    }

    public void deleteGame(int gameId) throws SQLException {
        String sql = "DELETE FROM game WHERE id = ?";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, gameId);
            ps.executeUpdate();
        }
    }

    public void forfeit(int gameId, int winnerId) throws SQLException {
        String sql = "UPDATE game SET status = 'finished', winner_id = ? WHERE id = ?";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, winnerId);
            ps.setInt(2, gameId);
            ps.executeUpdate();
        }
    }
}
