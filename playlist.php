<?php
// playlist.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/assets/locales/trad.php';

use App\Database;
use App\Playlist;
use App\Project;

if (!isset($_GET['id'])) {
    redirect_error('404', 'ID de liste manquant.');
}
$playlistId = (int) $_GET['id'];

$db          = new Database();
$pdo         = $db->getPDO();
$playlistObj = new Playlist($pdo);
$projectObj  = new Project($pdo);

$playlist = $playlistObj->getById($playlistId);
if (!$playlist || $playlist['user_id'] !== $_SESSION['user']['id']) {
    redirect_error('403', 'Accès refusé à cette liste.');
}

// Ajout d'un chant par ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $pid  = (int) ($_POST['project_id'] ?? 0);
    $note = trim($_POST['note'] ?? '') ?: null;
    if ($pid > 0) {
        $playlistObj->addItem($playlistId, $pid, $note);
        header("Location: playlist.php?id={$playlistId}&lang={$lang}");
        exit;
    }
}

// Suppression d'un chant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $itemId = (int) ($_POST['item_id'] ?? 0);
    if ($itemId > 0) {
        $playlistObj->removeItem($itemId);
        header("Location: playlist.php?id={$playlistId}&lang={$lang}");
        exit;
    }
}

// Import depuis fichier JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_playlist']) && !empty($_FILES['import_file']['tmp_name'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $jsonContent = file_get_contents($_FILES['import_file']['tmp_name']);
    $importData = json_decode($jsonContent, true);
    $importedCount = 0;
    $notFoundCount = 0;
    
    if ($importData && isset($importData['items'])) {
        foreach ($importData['items'] as $item) {
            $proj = null;
            if (!empty($item['project_id'])) {
                $proj = $projectObj->getProjectById((int)$item['project_id']);
            }
            if (!$proj && !empty($item['title'])) {
                $results = $projectObj->searchProjects($item['title']);
                $proj = $results[0] ?? null;
            }
            if ($proj) {
                $playlistObj->addItem($playlistId, $proj['id'], $item['note'] ?? null);
                $importedCount++;
            } else {
                $notFoundCount++;
            }
        }
    }
    $_SESSION['import_result'] = ['imported' => $importedCount, 'not_found' => $notFoundCount];
    header("Location: playlist.php?id={$playlistId}&lang={$lang}");
    exit;
}

// Générer un lien de partage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_share'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        redirect_error('403', 'Action non autorisée (CSRF).');
    }
    $playlistObj->setShareToken($playlistId, $_SESSION['user']['id']);
    header("Location: playlist.php?id={$playlistId}&lang={$lang}");
    exit;
}

// Recharger après actions
$playlist = $playlistObj->getById($playlistId);
$items    = $playlistObj->getItems($playlistId);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="admin-dashboard">
  <h1>Ma Liste : <?= htmlspecialchars($playlist['name']) ?><?php if ($playlist['event_date']): ?> — <small><?= (new DateTime($playlist['event_date']))->format('d/m/Y') ?></small><?php endif; ?></h1>

  <!-- Recherche par auto-complétion -->
  <section class="animated-form" style="max-width: 800px; margin: 1rem auto;">
    <form method="post" class="add-item-form" novalidate style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
      <?php csrf_input(); ?>
      <div class="form-group" style="--delay:0.1s; flex:2; position:relative;">
        <input type="text" id="searchPartition" placeholder=" " autocomplete="off">
        <label for="searchPartition">Rechercher une partition (titre ou auteur)</label>
        <div class="form-line"></div>
        <div id="autocompleteResults" style="position:absolute; top:100%; left:0; right:0; background:var(--card-bg); border:1px solid var(--border-color); max-height:200px; overflow-y:auto; display:none; z-index:100;"></div>
        <input type="hidden" name="project_id" id="selectedProjectId" value="">
      </div>
      <div class="form-group" style="--delay:0.2s; flex:1;">
        <input type="text" name="note" id="note" placeholder=" ">
        <label for="note">Note (tonalité, remarques…)</label>
        <div class="form-line"></div>
      </div>
      <button type="submit" name="add_item" class="btn-primary">Ajouter</button>
    </form>
  </section>

  <!-- Actions : Partage, Export & Impression -->
  <section style="max-width: 900px; margin: 1rem auto; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
    <button type="button" class="btn-secondary" onclick="window.print();">🖨️ Imprimer</button>
    <div class="dropdown" style="position:relative; display:inline-block;">
      <button type="button" class="btn-secondary" onclick="this.nextElementSibling.classList.toggle('show');">📥 Exporter ▼</button>
        <div class="dropdown-menu" style="display:none; position:absolute; top:100%; left:0; background:var(--card-bg); border:1px solid var(--border-color); border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:100; min-width:150px;">
          <a href="api/export-playlist.php?id=<?= $playlistId ?>&format=json" style="display:block; padding:8px 12px; text-decoration:none; color:var(--text-color);">📄 JSON</a>
          <a href="api/export-playlist.php?id=<?= $playlistId ?>&format=csv" style="display:block; padding:8px 12px; text-decoration:none; color:var(--text-color);">📊 CSV (Excel)</a>
          <a href="api/export-playlist.php?id=<?= $playlistId ?>&format=txt" style="display:block; padding:8px 12px; text-decoration:none; color:var(--text-color);">📝 Texte</a>
        </div>
    </div>
    <button type="button" class="btn-secondary" onclick="document.getElementById('importModal').style.display='flex';">📤 Importer</button>
    <?php if (!empty($playlist['share_token'])): ?>
      <?php $shareUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/share.php?token=' . urlencode($playlist['share_token']); ?>
      <span style="display:flex; align-items:center; gap:5px;">
        <input type="text" id="shareLink" value="<?= htmlspecialchars($shareUrl) ?>" readonly style="padding:5px; width:300px; font-size:0.85rem; border:1px solid var(--border-color);">
        <button type="button" class="btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('shareLink').value); window.showToast('Lien copié !', 'info');">📋 Copier</button>
      </span>
    <?php else: ?>
      <form method="post" style="display:inline;">
        <?php csrf_input(); ?>
        <button type="submit" name="generate_share" class="btn-secondary">🔗 Générer un lien de partage</button>
      </form>
    <?php endif; ?>
  </section>

  <!-- Liste des chants avec drag & drop -->
  <section style="max-width: 900px; margin: 1rem auto;">
    <?php if (empty($items)): ?>
      <p>Aucun chant dans cette liste pour le moment.</p>
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse;" id="playlistTable">
        <thead>
          <tr>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color); width:30px;">⠿</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color);">#</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color);">Titre</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color);">Moment</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color);">Temps</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border-color);">Note</th>
            <th style="padding:8px; border-bottom:1px solid var(--border-color);">Actions</th>
          </tr>
        </thead>
        <tbody id="playlistBody">
          <?php foreach ($items as $idx => $it): ?>
            <tr draggable="true" data-item-id="<?= $it['item_id'] ?>" class="playlist-row">
              <td style="padding:8px; cursor:grab;">⠿</td>
              <td style="padding:8px;" class="row-order"><?= $idx + 1 ?></td>
              <td style="padding:8px;">
                <a href="project.php?id=<?= $it['id'] ?>&lang=<?= $lang ?>">
                  <?= htmlspecialchars($it['title']) ?>
                </a>
              </td>
              <td style="padding:8px;"><?= htmlspecialchars($it['moment_messe'] ?? '') ?></td>
              <td style="padding:8px;"><?= htmlspecialchars($it['temps_liturgique'] ?? '') ?></td>
              <td style="padding:8px;"><?= htmlspecialchars($it['note'] ?? '') ?></td>
              <td style="padding:8px; text-align:center;">
                <form method="post" onsubmit="return confirm('Retirer ce chant de la liste ?');" style="display:inline-block;">
                  <?php csrf_input(); ?>
                  <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                  <button type="submit" name="remove_item" class="btn-danger" style="background:transparent; color:red; border:none; cursor:pointer;">Retirer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <?php if (isset($_SESSION['import_result'])): ?>
    <div class="success-message" style="max-width:900px; margin:1rem auto;">
      ✅ Import terminé : <?= $_SESSION['import_result']['imported'] ?> chant(s) ajouté(s)
      <?php if ($_SESSION['import_result']['not_found'] > 0): ?>
        , <?= $_SESSION['import_result']['not_found'] ?> non trouvé(s)
      <?php endif; ?>
    </div>
    <?php unset($_SESSION['import_result']); ?>
  <?php endif; ?>
</main>

<!-- Modal Import -->
<div id="importModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
  <div style="background:var(--card-bg); padding:2rem; border-radius:12px; max-width:500px; width:90%;">
    <h3 style="margin-bottom:1rem;">📤 Importer une playlist</h3>
    <p style="margin-bottom:1rem; opacity:0.8;">Sélectionnez un fichier JSON exporté depuis Partithéco.</p>
    <form method="post" enctype="multipart/form-data">
      <?php csrf_input(); ?>
      <input type="file" name="import_file" accept=".json" required style="margin-bottom:1rem; width:100%;">
      <div style="display:flex; gap:1rem; justify-content:flex-end;">
        <button type="button" class="btn-secondary" onclick="document.getElementById('importModal').style.display='none';">Annuler</button>
        <button type="submit" name="import_playlist" class="btn-primary">Importer</button>
      </div>
    </form>
  </div>
</div>

<script src="assets/js/base.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const playlistId = <?= (int) $playlistId ?>;

  // === Auto-complétion ===
  const searchInput = document.getElementById('searchPartition');
  const resultsDiv  = document.getElementById('autocompleteResults');
  const hiddenId    = document.getElementById('selectedProjectId');
  let acTimer;

  searchInput?.addEventListener('input', () => {
    clearTimeout(acTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) { resultsDiv.style.display = 'none'; return; }
    acTimer = setTimeout(async () => {
      try {
        const resp = await fetch('/api/projects/autocomplete.php?q=' + encodeURIComponent(q));
        const data = await resp.json();
        if (data.length === 0) { resultsDiv.style.display = 'none'; return; }
        resultsDiv.innerHTML = '';
        data.forEach(p => {
          const div = document.createElement('div');
          div.style.cssText = 'padding:8px 10px; cursor:pointer; border-bottom:1px solid var(--border-color);';
          div.textContent = p.title + (p.author ? ' — ' + p.author : '') + (p.moment_messe ? ' [' + p.moment_messe + ']' : '');
          div.addEventListener('click', () => {
            hiddenId.value = p.id;
            searchInput.value = p.title;
            resultsDiv.style.display = 'none';
          });
          div.addEventListener('mouseenter', () => div.style.background = 'var(--border-color)');
          div.addEventListener('mouseleave', () => div.style.background = '');
          resultsDiv.appendChild(div);
        });
        resultsDiv.style.display = 'block';
      } catch (e) { console.error(e); }
    }, 250);
  });

  document.addEventListener('click', (e) => {
    if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
      resultsDiv.style.display = 'none';
    }
  });

  // === Drag & Drop pour réordonner ===
  const tbody = document.getElementById('playlistBody');
  if (tbody) {
    let dragRow = null;

    tbody.addEventListener('dragstart', (e) => {
      dragRow = e.target.closest('tr');
      if (dragRow) {
        dragRow.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
      }
    });

    tbody.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      const target = e.target.closest('tr');
      if (target && target !== dragRow && tbody.contains(target)) {
        const rect = target.getBoundingClientRect();
        const mid  = rect.top + rect.height / 2;
        if (e.clientY < mid) {
          tbody.insertBefore(dragRow, target);
        } else {
          tbody.insertBefore(dragRow, target.nextSibling);
        }
      }
    });

    tbody.addEventListener('dragend', async () => {
      if (dragRow) dragRow.style.opacity = '1';
      dragRow = null;
      // Mettre à jour les numéros et persister
      const rows = tbody.querySelectorAll('tr');
      const itemIds = [];
      rows.forEach((row, i) => {
        row.querySelector('.row-order').textContent = i + 1;
        itemIds.push(parseInt(row.dataset.itemId));
      });
      try {
        await fetch('/api/projects/reorderPlaylist.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ playlist_id: playlistId, item_ids: itemIds })
        });
      } catch (e) { console.error('Erreur réordonnancement:', e); }
    });
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
