(function ($, Core) {
  const TARGET_SCORE = 10;
  const SPAWN_MIN_MS = 400;
  const SPAWN_MAX_MS = 1200;
  const VISIBLE_MS = 7000;

  const $score = $('#score-value');
  const $status = $('#status-text');
  const $gameArea = $('#game-area');
  const $mole = $('#mole');
  const $fallback = $('#mole-fallback');

  let $activeTarget = $mole;

  const state = {
    score: 0,
    isRunning: true,
    currentMoleId: 0,
    spawnTimer: null,
    hideTimer: null
  };

  $mole.on('error', function () {
    $mole.addClass('hidden');
    $fallback.removeClass('hidden');
    $activeTarget = $fallback;
  });

  function clearTimers() {
    clearTimeout(state.spawnTimer);
    clearTimeout(state.hideTimer);
    state.spawnTimer = null;
    state.hideTimer = null;
  }

  function renderScore() {
    $score.text(String(state.score));
  }

  function hideMole() {
    $mole.addClass('hidden');
    $fallback.addClass('hidden');
  }

  function scheduleSpawn() {
    if (!state.isRunning) return;
    const delay = Core.randomInt(SPAWN_MIN_MS, SPAWN_MAX_MS, Math.random);
    state.spawnTimer = setTimeout(spawnMole, delay);
  }

  function spawnMole() {
    if (!state.isRunning) return;
    state.currentMoleId += 1;

    const pos = Core.clampSpawnPosition(
      $gameArea.innerWidth(),
      $gameArea.innerHeight(),
      $activeTarget.outerWidth(),
      $activeTarget.outerHeight(),
      Math.random
    );

    $activeTarget
      .attr('data-mole-id', String(state.currentMoleId))
      .css({ left: pos.left + 'px', top: pos.top + 'px' })
      .removeClass('hidden');

    state.hideTimer = setTimeout(function () {
      hideMole();
      scheduleSpawn();
    }, VISIBLE_MS);
  }

  function onClick() {
    const clickedId = Number($(this).attr('data-mole-id'));
    const result = Core.resolveHit({
      isRunning: state.isRunning,
      currentMoleId: state.currentMoleId,
      clickedMoleId: clickedId,
      score: state.score,
      targetScore: TARGET_SCORE
    });

    if (!result.counted) return;

    state.score = result.score;
    renderScore();
    hideMole();

    clearTimeout(state.hideTimer);
    state.hideTimer = null;

    if (result.won) {
      state.isRunning = false;
      clearTimers();
      $status.text('You won! Final score: 10');
      return;
    }

    scheduleSpawn();
  }

  $mole.on('click', onClick);
  $fallback.on('click', onClick);

  renderScore();
  scheduleSpawn();
}(jQuery, window.GameCore));