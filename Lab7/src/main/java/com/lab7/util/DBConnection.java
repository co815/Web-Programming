package com.lab7.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBConnection {
    private static String url;
    private static String user;
    private static String password;

    public static void init(String url, String user, String password) {
        DBConnection.url = url;
        DBConnection.user = user;
        DBConnection.password = password;
    }

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(url, user, password);
    }
}
