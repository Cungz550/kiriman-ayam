// Ambil angka dari session via API
function fetchAngka() {
    fetch('api/get_angka.php')
      .then(res => res.json())
      .then(data => {
        console.log('📦 Angka dari session:', data.data);
        console.log('📊 Jumlah:', data.jumlah);
  
        // (Opsional) Update tampilan progress bar kalau mau dynamic
        updateProgressBar(data.jumlah);
      })
      .catch(err => {
        console.error('❌ Gagal ambil angka:', err);
      });
  }
  
  // Update progress bar secara dinamis (kalau dipanggil dari fetch)
  function updateProgressBar(jumlah) {
    const progressFill = document.getElementById('progress-fill');
    const countEl = document.getElementById('angka-count');
  
    const percent = (jumlah / 200) * 100;
    countEl.textContent = jumlah;
    progressFill.style.width = percent + '%';
  
    progressFill.classList.remove('bg-red-500', 'bg-yellow-400', 'bg-green-500');
    if (jumlah > 150) {
      progressFill.classList.add('bg-green-500');
    } else if (jumlah >= 51) {
      progressFill.classList.add('bg-yellow-400');
    } else {
      progressFill.classList.add('bg-red-500');
    }
  }
  
  // Bisa panggil otomatis saat halaman dibuka
  document.addEventListener('DOMContentLoaded', () => {
    fetchAngka(); // ambil angka langsung pas halaman load
  });

  function submitSemuaAngka() {
    fetch('api/submit_data.php', {
      method: 'POST',
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          alert('Data berhasil disimpan ke database!');
          fetchAngka(); // Refresh tampilan progress bar
        } else {
          alert('❌ Gagal simpan: ' + data.message);
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('❌ Terjadi kesalahan saat submit');
      });
  }
  
  const toggle = document.getElementById('darkModeToggle');
const kirimanSelect = new Choices('#kiriman', {
    searchEnabled: true,
    itemSelectText: '', // Biar ga ada teks "Press to select"
});

// Cek preferensi awal dari localStorage
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
}

// Toggle dark mode saat tombol diklik
toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    // Simpen preferensi ke localStorage
    if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
});
