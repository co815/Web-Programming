(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.GameCore = factory();
  }
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  function randomInt(min, max, rng) {
    const random = rng || Math.random;
    return Math.floor(random() * (max - min + 1)) + min;
  }

  function clampSpawnPosition(viewW, viewH, moleW, moleH, rng) {
    const maxLeft = Math.max(0, viewW - moleW);
    const maxTop = Math.max(0, viewH - moleH);
    return {
      left: randomInt(0, maxLeft, rng),
      top: randomInt(0, maxTop, rng)
    };
  }

  function shouldCountHit(isRunning, currentMoleId, clickedMoleId) {
    return isRunning === true
      && Number.isInteger(currentMoleId)
      && Number.isInteger(clickedMoleId)
      && Number.isFinite(currentMoleId)
      && Number.isFinite(clickedMoleId)
      && currentMoleId === clickedMoleId;
  }

  function nextScore(currentScore, targetScore) {
    const score = Number(currentScore) + 1;
    return { score, won: score === Number(targetScore) };
  }

  function resolveHit(payload) {
    const valid = shouldCountHit(payload.isRunning, payload.currentMoleId, payload.clickedMoleId);
    if (!valid) {
      return { counted: false, score: Number(payload.score), won: false };
    }
    const progressed = nextScore(payload.score, payload.targetScore);
    return { counted: true, score: progressed.score, won: progressed.won };
  }

  return {
    randomInt,
    clampSpawnPosition,
    shouldCountHit,
    nextScore,
    resolveHit
  };
}));