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
        assertEquals('\0', WinChecker.checkResult("-O--X----"));
    }

    @Test
    void xWinsFirstRow() {
        assertEquals('X', WinChecker.checkResult("XXX-O-O--"));
    }

    @Test
    void oWinsFirstColumn() {
        assertEquals('O', WinChecker.checkResult("OX-OX-OX-"));
    }

    @Test
    void xWinsMainDiagonal() {
        assertEquals('X', WinChecker.checkResult("X-O-X-O-X"));
    }

    @Test
    void oWinsAntiDiagonal() {
        assertEquals('O', WinChecker.checkResult("X-OXO-OX-"));
    }

    @Test
    void drawWhenBoardFullNoWinner() {
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
