let pollInterval = null;
let currentState = null;
let gameOver= false;

function startPolling() {
    fetchState();
    pollInterval = setInterval(fetchState, 1500);
}

function fetchState() {
    fetch(contextPath + '/gamestate')
        .then(function(r) { return r.json(); })
        .then(function(state) { applyState(state); })
        .catch(function() { setStatus('Connection error. Retrying...'); });
}

function applyState(state) {
    currentState = state;

    if (state.status === 'no_game') {
        clearInterval(pollInterval);
        window.location.href = contextPath + '/lobby';
        return;
    }

    renderBoard(state.board, state.turn === playerSymbol && state.status === 'active');

    if (state.status === 'finished') {
        gameOver = true;
        clearInterval(pollInterval);
        var msg;
        if (!state.winnerName) {
            msg = "It's a draw!";
        } else if (state.winnerName === myUsername) {
            msg = 'You win!';
        } else {
            msg = state.winnerName + ' wins!';
        }
        showOverlay(msg);
        return;
    }

    if (state.status === 'waiting') {
        setStatus('Waiting for an opponent to join...');
        return;
    }

    if (state.turn === playerSymbol) {
        setStatus('Your turn (' + playerSymbol + ')');
    } else {
        var opponentName = playerSymbol === 'X' ? state.playerOName : state.playerXName;
        setStatus("Waiting for " + opponentName + "'s move...");
    }
}

function renderBoard(board, clickable) {
    var cells = document.querySelectorAll('.cell');
    cells.forEach(function(cell, i) {
        var ch = board[i];
        cell.textContent = ch === '-' ? '' : ch;
        cell.className = 'cell';
        if (ch === 'X') cell.classList.add('x');
        if (ch === 'O') cell.classList.add('o');
        if (clickable && ch === '-' && !gameOver) {
            cell.classList.add('clickable');
        }
    });
}

function makeMove(index) {
    if (gameOver || !currentState) return;
    if (currentState.status !== 'active') return;
    if (currentState.turn !== playerSymbol) return;
    if (currentState.board[index] !== '-') return;

    fetch(contextPath + '/move', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'position=' + index
    })
    .then(function(r) { return r.json(); })
    .then(function(state) { applyState(state); })
    .catch(function() { setStatus('Move failed. Please try again.'); });
}

function setStatus(msg) {
    document.getElementById('status-bar').textContent = msg;
}

function showOverlay(msg) {
    document.getElementById('overlay-message').textContent = msg;
    document.getElementById('overlay').classList.remove('hidden');
}

function confirmLeave() {
    if (confirm('Leave game? You will forfeit if the game is active.')) {
        document.getElementById('leaveForm').submit();
    }
}

startPolling();
