<?php
// calendrier.php - Calendrier liturgique interactif
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\LiturgicalCalendar;

$currentMonth = (int)($_GET['month'] ?? date('n'));
$currentYear = (int)($_GET['year'] ?? date('Y'));

$moisFr = ['', 'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];

// Générer le calendrier
$firstDay = new DateTime("$currentYear-$currentMonth-01");
$daysInMonth = (int)$firstDay->format('t');
$startDayOfWeek = (int)$firstDay->format('N'); // 1=Lundi, 7=Dimanche
$monthData = LiturgicalCalendar::getMonthData($currentYear, $currentMonth);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="calendrier-page">
  <section class="calendrier-header">
    <h1>📅 Calendrier Liturgique</h1>
    <p class="subtitle">Planifiez vos célébrations et trouvez les chants adaptés</p>
  </section>

  <section class="calendrier-nav">
    <?php
      $prevMonth = $currentMonth - 1;
      $prevYear = $currentYear;
      if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
      $nextMonth = $currentMonth + 1;
      $nextYear = $currentYear;
      if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
    ?>
    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&lang=<?= $lang ?>" class="btn-nav">← Précédent</a>
    <h2><?= $moisFr[$currentMonth] ?> <?= $currentYear ?></h2>
    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&lang=<?= $lang ?>" class="btn-nav">Suivant →</a>
  </section>

  <section class="calendrier-legend">
    <span class="legend-item avent">Avent</span>
    <span class="legend-item noel">Noël</span>
    <span class="legend-item careme">Carême</span>
    <span class="legend-item paques">Pâques</span>
    <span class="legend-item ordinaire">Temps Ordinaire</span>
  </section>

  <div class="calendrier-grid">
    <div class="day-header">Lun</div>
    <div class="day-header">Mar</div>
    <div class="day-header">Mer</div>
    <div class="day-header">Jeu</div>
    <div class="day-header">Ven</div>
    <div class="day-header">Sam</div>
    <div class="day-header dimanche">Dim</div>

    <?php
    // Cases vides avant le premier jour
    for ($i = 1; $i < $startDayOfWeek; $i++): ?>
      <div class="day-cell empty"></div>
    <?php endfor;

    // Jours du mois
    for ($day = 1; $day <= $daysInMonth; $day++):
      $date = new DateTime($monthData[$day]['date']);
      $dayOfWeek = (int)$monthData[$day]['dayOfWeek'];
      $temps = $monthData[$day]['season'];
      $tempsClass = strtolower($temps);
      $isDimanche = (bool)$monthData[$day]['isSunday'];
      $isToday = ($date->format('Y-m-d') === date('Y-m-d'));
    ?>
      <div class="day-cell <?= $tempsClass ?> <?= $isDimanche ? 'dimanche' : '' ?> <?= $isToday ? 'today' : '' ?>"
           data-date="<?= $date->format('Y-m-d') ?>"
           data-temps="<?= $temps ?>"
           onclick="showDayDetails(this)">
        <span class="day-number"><?= $day ?></span>
        <?php if ($isDimanche): ?>
          <span class="day-label">🎵</span>
        <?php endif; ?>
      </div>
    <?php endfor;

    // Cases vides après le dernier jour
    $lastDayOfWeek = (int)(new DateTime("$currentYear-$currentMonth-$daysInMonth"))->format('N');
    for ($i = $lastDayOfWeek + 1; $i <= 7; $i++): ?>
      <div class="day-cell empty"></div>
    <?php endfor; ?>
  </div>

  <div id="dayModal" class="day-modal" style="display:none;">
    <div class="modal-content">
      <button class="modal-close" onclick="closeDayModal()">×</button>
      <h3 id="modalDate"></h3>
      <p id="modalTemps" class="modal-temps"></p>
      <div id="modalSuggestions"></div>
      <a id="modalLink" href="#" class="btn-primary">Voir tous les chants</a>
    </div>
  </div>
</main>

<style>
.calendrier-page { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }
.calendrier-header { text-align: center; margin-bottom: 1.5rem; }
.calendrier-header h1 { font-family: var(--font-family-serif); color: var(--color-lit-night); }
[data-theme="dark"] .calendrier-header h1 { color: var(--color-lit-gold); }
[data-theme="dark"] .day-header { color: var(--color-lit-gold); }
[data-theme="dark"] .calendrier-nav h2 { color: var(--color-lit-gold); }
[data-theme="dark"] .btn-nav { background: var(--card-bg); color: var(--text-color); border-color: var(--border-color); }

.calendrier-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.calendrier-nav h2 { font-family: var(--font-family-serif); }
.btn-nav { padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; text-decoration: none; color: var(--text-color); }

.calendrier-legend { display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.5rem; flex-wrap: wrap; }
.legend-item { padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem; }
.legend-item.avent { background: #7b68ee; color: white; }
.legend-item.noel { background: #ffd700; color: #333; }
.legend-item.careme { background: #9370db; color: white; }
.legend-item.paques { background: #ff6347; color: white; }
.legend-item.ordinaire { background: #3cb371; color: white; }

.calendrier-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; background: var(--card-bg); padding: 1rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.day-header { text-align: center; font-weight: bold; padding: 0.5rem; color: var(--color-lit-night); }
.day-header.dimanche { color: var(--color-lit-accent); }

.day-cell { min-height: 60px; padding: 0.5rem; border-radius: 8px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; position: relative; }
.day-cell:not(.empty):hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10; }
.day-cell.empty { background: transparent; cursor: default; }
.day-cell.avent { background: rgba(123, 104, 238, 0.2); }
.day-cell.noël, .day-cell.noel { background: rgba(255, 215, 0, 0.3); }
.day-cell.carême, .day-cell.careme { background: rgba(147, 112, 219, 0.2); }
.day-cell.pâques, .day-cell.paques { background: rgba(255, 99, 71, 0.2); }
.day-cell.ordinaire { background: rgba(60, 179, 113, 0.15); }
.day-cell.dimanche { border: 2px solid var(--color-lit-gold); }
.day-cell.today { box-shadow: inset 0 0 0 3px var(--color-lit-accent); }

.day-number { font-weight: bold; }
.day-label { position: absolute; bottom: 4px; right: 4px; font-size: 0.8rem; }

.day-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: var(--card-bg); padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; position: relative; }
.modal-close { position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
.modal-temps { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
#modalSuggestions { margin: 1rem 0; }
</style>

<script>
function showDayDetails(cell) {
  const date = cell.dataset.date;
  const temps = cell.dataset.temps;
  
  document.getElementById('modalDate').textContent = new Date(date).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  document.getElementById('modalTemps').textContent = 'Temps liturgique : ' + temps;
  document.getElementById('modalTemps').className = 'modal-temps legend-item ' + temps.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  document.getElementById('modalLink').href = 'publications.php?temps=' + encodeURIComponent(temps) + '&lang=<?= $lang ?>';
  document.getElementById('modalSuggestions').innerHTML = '<p>Cliquez ci-dessous pour voir les chants suggérés pour ce temps liturgique.</p>';
  document.getElementById('dayModal').style.display = 'flex';
}

function closeDayModal() {
  document.getElementById('dayModal').style.display = 'none';
}

document.getElementById('dayModal').addEventListener('click', (e) => {
  if (e.target.id === 'dayModal') closeDayModal();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
