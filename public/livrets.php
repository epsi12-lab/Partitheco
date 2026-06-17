<?php
// livrets.php - Page des livrets mensuels téléchargeables
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lang/trad.php';

$currentMonth = date('m');
$currentYear = date('Y');

$moisFr = [
    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
    '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
    '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
];

$tempsLiturgiques = ['Avent', 'Noël', 'Carême', 'Pâques', 'Ordinaire'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<main class="livrets-page">
  <section class="livrets-header">
    <h1>📚 Livrets Mensuels</h1>
    <p class="subtitle">Téléchargez les compilations de partitions par mois</p>
  </section>

  <section class="livrets-selector">
    <form id="livretForm" class="livret-form">
      <div class="form-row">
        <div class="form-group">
          <label for="monthSelect">Mois</label>
          <select id="monthSelect" name="month">
            <?php foreach ($moisFr as $num => $nom): ?>
              <option value="<?= $num ?>" <?= $num === $currentMonth ? 'selected' : '' ?>><?= $nom ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="yearSelect">Année</label>
          <select id="yearSelect" name="year">
            <?php for ($y = (int)$currentYear; $y >= 2020; $y--): ?>
              <option value="<?= $y ?>"><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="tempsSelect">Temps liturgique (optionnel)</label>
          <select id="tempsSelect" name="temps">
            <option value="">Tous les temps</option>
            <?php foreach ($tempsLiturgiques as $t): ?>
              <option value="<?= $t ?>"><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primary">📖 Générer le livret</button>
      </div>
    </form>
  </section>

  <section id="livretPreview" class="livret-preview" style="display:none;">
    <div class="livret-header-preview">
      <h2 id="livretTitle"></h2>
      <p id="livretCount"></p>
    </div>
    <div id="livretContent" class="livret-content"></div>
    <div class="livret-actions">
      <button id="printLivret" class="btn-secondary">🖨️ Imprimer</button>
      <button id="downloadLivret" class="btn-primary">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>
  </section>
</main>

<style>
.livrets-page { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
.livrets-header { text-align: center; margin-bottom: 2rem; }
.livrets-header h1 { font-family: var(--font-family-serif); color: var(--color-lit-night); }
[data-theme="dark"] .livrets-header h1 { color: var(--color-lit-gold); }
.livrets-header .subtitle { color: #666; }
[data-theme="dark"] .livrets-header .subtitle { color: var(--text-muted); }

.livrets-selector { background: var(--card-bg); padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.livret-form .form-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.livret-form .form-group { flex: 1; min-width: 150px; }
.livret-form label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
.livret-form select { width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem; }

.livret-preview { background: var(--card-bg); padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.livret-header-preview { text-align: center; border-bottom: 2px solid var(--color-lit-gold); padding-bottom: 1rem; margin-bottom: 1.5rem; }
.livret-header-preview h2 { color: var(--color-lit-night); font-family: var(--font-family-serif); }
[data-theme="dark"] .livret-header-preview h2 { color: var(--color-lit-gold); }

.livret-content { display: grid; gap: 1rem; }
.livret-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--background); border-radius: 8px; border-left: 4px solid var(--color-lit-gold); }
.livret-item-info h4 { margin: 0 0 0.25rem; }
.livret-item-info p { margin: 0; font-size: 0.9rem; color: #666; }
[data-theme="dark"] .livret-item-info p { color: var(--text-muted); }
.livret-item-tags { display: flex; gap: 0.5rem; }
.livret-item-tags span { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; background: var(--color-lit-gold); color: white; }

.livret-actions { display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); }

@media print {
  .livrets-selector, .livret-actions, .main_navbar, footer { display: none !important; }
  .livret-preview { box-shadow: none; }
}
</style>

<script>
document.getElementById('livretForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const month = document.getElementById('monthSelect').value;
  const year = document.getElementById('yearSelect').value;
  const temps = document.getElementById('tempsSelect').value;
  
  const url = `api/livrets.php?month=${month}&year=${year}${temps ? '&temps=' + encodeURIComponent(temps) : ''}`;
  
  try {
    const res = await fetch(url);
    const data = await res.json();
    
    if (data.success) {
      document.getElementById('livretTitle').textContent = data.livret.titre;
      document.getElementById('livretCount').textContent = `${data.livret.total} partition(s)`;
      
      const content = document.getElementById('livretContent');
      content.innerHTML = data.livret.partitions.map(p => `
        <div class="livret-item">
          <div class="livret-item-info">
            <h4>${p.title}</h4>
            <p>${p.author || 'Auteur inconnu'}</p>
          </div>
          <div class="livret-item-tags">
            ${p.moment_messe ? `<span>${p.moment_messe}</span>` : ''}
            ${p.temps_liturgique ? `<span>${p.temps_liturgique}</span>` : ''}
          </div>
        </div>
      `).join('');
      
      document.getElementById('livretPreview').style.display = 'block';
    }
  } catch (err) {
    console.error('Erreur:', err);
  }
});

document.getElementById('printLivret').addEventListener('click', () => window.print());
document.getElementById('downloadLivret').addEventListener('click', () => window.print());
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
