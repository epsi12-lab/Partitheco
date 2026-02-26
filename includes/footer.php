<?php
// includes/footer.php
?>
<footer class="main-footer">
  <button id="backToTop" title="Haut de page">↑</button>

  <div class="footer-container">

    <div class="footer-info">
      <h2>PARTITHECO</h2>
      <h4>Music Scores</h4>
    </div>

    <div class="footer-social">
      <a href="https://www.youtube.com/" target="_blank" rel="noopener">
        <img src="assets/static/youtube-brands.svg" alt="YouTube">
      </a>
      <a href="https://github.com/" target="_blank" rel="noopener">
        <img src="assets/static/github-brands.svg" alt="GitHub">
      </a>
      <a href="https://www.instagram.com/" target="_blank" rel="noopener">
        <img src="assets/static/instagram-brands.svg" alt="Instagram">
      </a>
      <a href="https://www.facebook.com/" target="_blank" rel="noopener">
        <img src="assets/static/facebook-brands.svg" alt="Facebook">
      </a>
    </div>

    <div class="footer-about">
      <a href="about.php?lang=<?= $lang ?>"><?= htmlspecialchars($t['nav']['about']) ?></a>
    </div>

    <div class="footer-credit">
      <p><?= htmlspecialchars($t['footer']['credits']) ?></p>
      <p>&copy; <?= date("Y") ?> Partithéco</p>
    </div>

  </div>
</footer>

<script src="/assets/js/backToTop.js"></script>
