document.querySelectorAll('form.needs-check').forEach(form => {
  form.addEventListener('submit', e => {
    if (!form.checkValidity()) {
      e.preventDefault();
      form.classList.add('was-validated');
    }
  });
});

document.addEventListener('submit', e => {
  const msg = e.target.dataset.confirm;
  if (msg && !confirm(msg)) e.preventDefault();
});

const togglePw = document.getElementById('togglePw');
if (togglePw) {
  togglePw.addEventListener('click', () => {
    const input = document.getElementById('password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    togglePw.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
  });
}

document.querySelectorAll('.print-btn').forEach(btn => btn.addEventListener('click', () => window.print()));

const tbody = document.getElementById('patients');
if (tbody) {
  const $ = id => document.getElementById(id);
  const badges = { outpatient: 'bg-info', admitted: 'bg-warning text-dark', discharged: 'bg-secondary' };
  let timer;   

  async function loadPatients() {
    const params = new URLSearchParams({
      q: $('search').value, status: $('status').value,
      gender: $('gender').value, blood: $('blood').value
    });
    const res = await fetch('search.php?' + params);
    if (res.status === 401) { location.href = 'login.php'; return; }   
    const patients = await res.json();

    tbody.innerHTML = '';
    $('empty').classList.toggle('d-none', patients.length > 0);
    patients.forEach(p => tbody.appendChild(makeRow(p)));
  }

  function age(birth) {
    const b = new Date(birth), now = new Date();
    let years = now.getFullYear() - b.getFullYear();
    if (now < new Date(now.getFullYear(), b.getMonth(), b.getDate())) years--;
    return years;
  }

  function makeRow(p) {
    const tr = document.createElement('tr');
    const cell = (text, cls) => {
      const td = document.createElement('td');
      td.textContent = text;
      if (cls) td.className = cls;
      tr.appendChild(td);
      return td;
    };

    cell('P-' + String(p.id).padStart(4, '0'), 'text-muted');
    cell(p.first_name + ' ' + p.last_name, 'fw-semibold');
    cell(age(p.birth_date) + ' / ' + p.gender);
    cell(p.blood_type, 'd-none d-md-table-cell');
    cell(p.phone, 'd-none d-md-table-cell');

    const status = cell('');
    status.innerHTML = '<span class="badge"></span>';
    status.firstChild.textContent = p.status;
    status.firstChild.className = 'badge ' + (badges[p.status] || 'bg-secondary');


    const actions = cell('', 'text-end text-nowrap');
    actions.innerHTML = `
      <a class="btn btn-sm btn-primary" href="patient_view.php?id=${Number(p.id)}">View</a>
      <a class="btn btn-sm btn-outline-secondary" href="patient_form.php?id=${Number(p.id)}" title="Edit"><i class="bi bi-pencil"></i></a>`;
    if (ME.role === 'admin') {
      actions.insertAdjacentHTML('beforeend', `
        <form method="post" action="patient_delete.php" class="d-inline" data-confirm="Delete this patient and ALL their records?">
          <input type="hidden" name="id" value="${Number(p.id)}">
          <input type="hidden" name="csrf">
          <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
        </form>`);
      actions.querySelector('[name=csrf]').value = CSRF;
    }
    return tr;
  }

  $('search').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(loadPatients, 300); });
  ['status', 'gender', 'blood'].forEach(id => $(id).addEventListener('change', loadPatients));
  loadPatients();
}
