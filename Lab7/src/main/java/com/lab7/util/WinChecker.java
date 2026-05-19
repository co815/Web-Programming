package com.lab7.util;

public class WinChecker {

    private static final int[][] LINES = {
        {0, 1, 2}, {3, 4, 5}, {6, 7, 8}, // rows
        {0, 3, 6}, {1, 4, 7}, {2, 5, 8}, // columns
        {0, 4, 8}, {2, 4, 6}             // diagonals
    };

    /** Returns 'X', 'O', 'D' (draw), or '\0' (game still ongoing). */
    public static char checkResult(String board) {
        for (int[] line : LINES) {
            char c = board.charAt(line[0]);
            if (c != '-' && c == board.charAt(line[1]) && c == board.charAt(line[2])) {
                return c;
            }
        }
        if (board.indexOf('-') == -1) return 'D';
        return '\0';
    }

    /** Returns 'X' or 'O'. X always goes first. */
    public static char currentTurn(String board) {
        long xCount = board.chars().filter(c -> c == 'X').count();
        long oCount = board.chars().filter(c -> c == 'O').count();
        return xCount == oCount ? 'X' : 'O';
    }
}
