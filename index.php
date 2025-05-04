<?php
// index.php
session_start(); 

require_once __DIR__ . '/assets/locales/trad.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Project.php';
require_once __DIR__ . '/classes/Subscriber.php';


$successNL = false;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['newsletter_submit'])) {
    require_once __DIR__ . '/classes/Subscriber.php';
    $sub       = new Subscriber((new Database())->getPDO());
    $successNL = $sub->insert($_POST['newsletter_email']);
}


$db         = new Database();
$pdo        = $db->getPDO();
$projectObj = new Project($pdo);
$projects   = $projectObj->getProjects(5, 0);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <div id="projectsContainer">
    <?php $i = 0; ?>
    <?php foreach ($projects as $project): ?>
      <?php
        $file    = $project['thumbnail'] ?? '';
        $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $fileUrl = "assets/img/{$file}";
        $detail  = "project.php?id={$project['id']}&lang={$lang}";
      ?>
      <div class="project-item" style="--order: <?= $i++ ?>">
        <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
          <a href="<?= htmlspecialchars($detail) ?>">
            <img
              src="<?= htmlspecialchars($fileUrl) ?>"
              alt="<?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>"
              class="project-thumbnail">
          </a>
        <?php elseif ($ext === 'pdf'): ?>
          <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
            <img
              src="/assets/static/file-pdf-solid.svg"
              alt="PDF : <?= htmlspecialchars($project['title'],ENT_NOQUOTES, 'UTF-8', false) ?>"
              class="project-thumbnail pdf-icon">
          </a>
        <?php endif; ?>

        <h3>
          <a href="<?= htmlspecialchars($detail) ?>">
            <?= htmlspecialchars($project['title'], ENT_NOQUOTES, 'UTF-8', false) ?>
          </a>
        </h3>
        <p>
          <?= nl2br(htmlspecialchars(substr($project['description'], 0, 100,), ENT_NOQUOTES, 'UTF-8', false)) ?>…
        </p>
      </div>
    <?php endforeach; ?>
  </div>

  <button
    type="button"
    class="btn-load-more"
    onclick="location.href='publications.php?lang=<?= htmlspecialchars($lang) ?>'">
    <?= htmlspecialchars($t['buttons']['load_more']) ?>
  </button>

  <section id="mapWeatherSection">
    <div class="info-block" id="mapContainer">
      <h2>
        <?= htmlspecialchars($t['sections']['find_us']) ?>
      </h2>
      <div id="map"></div>
    </div>
    <div class="info-block" id="weatherContainer">
      <h2>
        <?= htmlspecialchars($t['sections']['weather']) ?>
      </h2>
      <div id="weather"></div>
    </div>
  </section>

  <section id="newsletter" class="animated-form centered-section">
    <h2 class="fade-in-up" style="--delay:0.1s;">
      <?= htmlspecialchars($t['newsletter']['title']) ?>
    </h2>

    <?php if (!empty($successNL)): ?>
      <p class="fade-in-up" style="--delay:0.2s;">
        <?= htmlspecialchars($t['newsletter']['success']) ?>
      </p>
    <?php else: ?>
      <?php $delay = 0.2; ?>
      <form method="post" class="newsletter-form" novalidate>
        <div class="form-group" style="--delay:<?= $delay ?>s">
          <input
            type="email"
            id="newsletterEmail"
            name="newsletter_email"
            placeholder=" "
            required>
          <label for="newsletterEmail">
            <?= htmlspecialchars($t['form']['email']) ?>
          </label>
          <div class="form-line"></div>
        </div>

        <?php $delay += 0.1; ?>
        <button
          type="submit"
          name="newsletter_submit"
          class="form-btn btn-primary"
          style="--delay:<?= $delay ?>s;">
          <?= htmlspecialchars($t['buttons']['subscribe']) ?>
        </button>
      </form>
    <?php endif; ?>
  </section>

</main>

<script src="assets/js/base.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const map = L.map('map').setView([48.57948, 7.76292], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  L.marker([48.57948, 7.76292])
   .addTo(map)
   .bindPopup('UFR Mathématiques & Info')
   .openPopup();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const apiKey = '74c0731dc2204b4acce5e8ec0e5d5d02'; 
  try {
    const resp = await fetch(
      `https://api.openweathermap.org/data/2.5/weather` +
      `?q=Strasbourg,FR&units=metric&lang=fr&appid=${apiKey}`
    );
    if (!resp.ok) throw new Error(`Erreur ${resp.status}`);
    const data = await resp.json();
    document.getElementById('weather').innerHTML = `
      <p>
        <strong>${data.name}</strong> : ${data.weather[0].description},
        ${data.main.temp}&deg;C
        <img src="https://openweathermap.org/img/wn/${data.weather[0].icon}.png"
             alt="${data.weather[0].description}">
      </p>`;
  } catch {
    document.getElementById('weather')
            .textContent = 'Météo indisponible';
  }
});
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>