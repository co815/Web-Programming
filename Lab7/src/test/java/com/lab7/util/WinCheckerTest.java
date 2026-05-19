package com.lab7.util;

import org.junit.jupiter.api.Test;
import static org.junit.jupiter.api.Assertions.*;

class WinCheckerTest {

    @Test
    void ongoingGameHasNoResult() {
        assertEquals('\0', WinChecker.checkResult("---------"));
    }

    @Test
    void ongoingPartialGameHasNoResult() {
        // X in center, O in corner, not finished
        assertEquals('\0', WinChecker.checkResult("-O--X----"));
    }

    @Test
    void xWinsFirstRow() {
        // XXX in positions 0,1,2
        assertEquals('X', WinChecker.checkResult("XXX-O-O--"));
    }

    @Test
    void oWinsFirstColumn() {
        // O at positions 0,3,6 — X at 1,4,7 (3X, 3O: valid end state)
        assertEquals('O', WinChecker.checkResult("OX-OX-OX-"));
    }

    @Test
    void xWinsMainDiagonal() {
        // X in positions 0,4,8
        assertEquals('X', WinChecker.checkResult("X-O-X-O-X"));
    }

    @Test
    void oWinsAntiDiagonal() {
        // O in positions 2,4,6
        assertEquals('O', WinChecker.checkResult("X-OXO-OX-"));
    }

    @Test
    void drawWhenBoardFullNoWinner() {
        // Board: XOXXOOOXX (no winner, no empty cells)
        // 0=X 1=O 2=X 3=X 4=O 5=O 6=O 7=X 8=X
        // Rows: XOX, XOO, OXX — none win
        // Cols: XXO, OOX, XOX — none win
        // Diags: XOX, XOO — none win
        assertEquals('D', WinChecker.checkResult("XOXXOOOXX"));
    }

    @Test
    void xTurnOnEmptyBoard() {
        assertEquals('X', WinChecker.currentTurn("---------"));
    }

    @Test
    void oTurnAfterOneXMove() {
        assertEquals('O', WinChecker.currentTurn("X--------"));
    }

    @Test
    void xTurnWhenMovesEqual() {
        assertEquals('X', WinChecker.currentTurn("XO-------"));
    }
}
