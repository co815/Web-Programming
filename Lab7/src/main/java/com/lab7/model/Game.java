package com.lab7.model;

public class Game {
    private int id;
    private int playerXId;
    private String playerXName;
    private Integer playerOId;
    private String playerOName;
    private String board;
    private String status;
    private Integer winnerId;
    private String winnerName;

    public Game() {}

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public int getPlayerXId() { return playerXId; }
    public void setPlayerXId(int playerXId) { this.playerXId = playerXId; }
    public String getPlayerXName() { return playerXName; }
    public void setPlayerXName(String playerXName) { this.playerXName = playerXName; }
    public Integer getPlayerOId() { return playerOId; }
    public void setPlayerOId(Integer playerOId) { this.playerOId = playerOId; }
    public String getPlayerOName() { return playerOName; }
    public void setPlayerOName(String playerOName) { this.playerOName = playerOName; }
    public String getBoard() { return board; }
    public void setBoard(String board) { this.board = board; }
    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }
    public Integer getWinnerId() { return winnerId; }
    public void setWinnerId(Integer winnerId) { this.winnerId = winnerId; }
    public String getWinnerName() { return winnerName; }
    public void setWinnerName(String winnerName) { this.winnerName = winnerName; }
}
