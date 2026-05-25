package com.lab7.util;

import jakarta.servlet.ServletContext;
import jakarta.servlet.ServletContextEvent;
import jakarta.servlet.ServletContextListener;
import jakarta.servlet.annotation.WebListener;

import java.io.File;
import java.io.FileInputStream;
import java.util.Properties;

@WebListener
public class AppContextListener implements ServletContextListener {

    @Override
    public void contextInitialized(ServletContextEvent sce) {
        Properties env = loadDotEnv();
        String url = get(env, "DB_URL");
        String user = get(env, "DB_USER");
        String password = get(env, "DB_PASSWORD");
        DBConnection.init(url, user, password);
    }

    @Override
    public void contextDestroyed(ServletContextEvent sce) {}

    private Properties loadDotEnv() {
        Properties props = new Properties();
        File envFile = new File(".env");
        if (envFile.exists()) {
            try (FileInputStream fis = new FileInputStream(envFile)) {
                props.load(fis);
            } catch (Exception ignored) {}
        }
        return props;
    }

    private String get(Properties env, String key) {
        String val = System.getenv(key);
        if (val != null) return val;
        return env.getProperty(key);
    }
}
