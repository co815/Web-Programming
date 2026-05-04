const GameCore = {
  randomInt: function(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  },

  clampSpawnPosition: function(viewW, viewH, moleW, moleH) {
    const maxLeft = Math.max(0, viewW - moleW);
    const maxTop = Math.max(0, viewH - moleH);
    return {
      left: this.randomInt(0, maxLeft),
      top: this.randomInt(0, maxTop)
    };
  },

  shouldCountHit: function(isRunning, currentMoleId, clickedMoleId) {
    return isRunning === true && currentMoleId === clickedMoleId;
  },

  nextScore: function(currentScore, targetScore) {
    const score = Number(currentScore) + 1;
    return { score, won: score === Number(targetScore) };
  },

  resolveHit: function(payload) {
    const valid = this.shouldCountHit(payload.isRunning, payload.currentMoleId, payload.clickedMoleId);
    if (!valid) {
      return { counted: false, score: Number(payload.score), won: false };
    }
    const progressed = this.nextScore(payload.score, payload.targetScore);
    return { counted: true, score: progressed.score, won: progressed.won };
  }
};