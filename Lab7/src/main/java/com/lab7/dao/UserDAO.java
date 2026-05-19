package com.lab7.dao;

import com.lab7.model.User;
import com.lab7.util.DBConnection;
import com.lab7.util.PasswordUtil;

import java.sql.*;

public class UserDAO {

    public User findByUsername(String username) throws SQLException {
        String sql = "SELECT id, username, password_hash FROM users WHERE username = ?";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, username);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    User u = new User();
                    u.setId(rs.getInt("id"));
                    u.setUsername(rs.getString("username"));
                    u.setPasswordHash(rs.getString("password_hash"));
                    return u;
                }
                return null;
            }
        }
    }

    public boolean create(String username, String password) throws SQLException {
        String sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, username);
            ps.setString(2, PasswordUtil.hash(password));
            return ps.executeUpdate() == 1;
        }
    }

    public User authenticate(String username, String password) throws SQLException {
        User u = findByUsername(username);
        if (u != null && PasswordUtil.verify(password, u.getPasswordHash())) {
            return u;
        }
        return null;
    }
}
