document.addEventListener('click', function(e){
  if (e.target.matches('.toggle-task')) {
    e.preventDefault();
    const id = e.target.dataset.id;
    fetch('/api/toggle_task.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(id)
    }).then(r => r.json()).then(j => {
      if (j.ok) location.reload();
    });
  }
});
