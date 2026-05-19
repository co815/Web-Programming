package com.lab7.util;

import jakarta.servlet.ServletContext;
import jakarta.servlet.ServletContextEvent;
import jakarta.servlet.ServletContextListener;
import jakarta.servlet.annotation.WebListener;

@WebListener
public class AppContextListener implements ServletContextListener {

    @Override
    public void contextInitialized(ServletContextEvent sce) {
        ServletContext ctx = sce.getServletContext();
        String password = System.getenv("DB_PASSWORD");
        if (password == null) password = ctx.getInitParameter("db.password");
        DBConnection.init(
            ctx.getInitParameter("db.url"),
            ctx.getInitParameter("db.user"),
            password
        );
    }

    @Override
    public void contextDestroyed(ServletContextEvent sce) {}
}
