document.addEventListener('DOMContentLoaded', () => {
  const navLinks = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.container.section[id]');

  // 1. MANAJEMEN KLIK NAVBAR (Smooth Scroll Manual)
  navLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');

      // Jika link menuju ke ID internal (contoh: #categories atau #popular-books)
      if (targetId && targetId.startsWith('#') && targetId.length > 1) {
        e.preventDefault(); // Hentikan lompatan mendadak bawaan browser

        const targetSection = document.querySelector(targetId);
        if (targetSection) {
          // Hitung tinggi sticky navbar (default 90px jika tidak terdeteksi)
          const navbarHeight = document.querySelector('.header')?.offsetHeight || 90;
          
          // Hitung posisi scroll yang tepat
          const elementPosition = targetSection.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - navbarHeight;

          // Eksekusi scroll halus secara pasti melalui JS
          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });

          // Update langsung indikator garis biru pada menu yang diklik
          navLinks.forEach(nav => nav.classList.remove('active'));
          this.classList.add('active');
        }
      }
    });
  });

  // 2. MANAJEMEN SCROLL OTOMATIS (Update Garis Biru Saat Layar Digerakkan)
  window.addEventListener('scroll', () => {
    let currentSectionId = '';
    const navbarHeight = document.querySelector('.header')?.offsetHeight || 90;

    // Cek posisi scroll saat ini terhadap setiap section
    sections.forEach(section => {
      const sectionTop = section.offsetTop - navbarHeight - 50; // Toleransi offset
      const sectionHeight = section.offsetHeight;

      if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
        currentSectionId = section.getAttribute('id');
      }
    });

    // Jika posisi layar masih paling atas (Hero / Beranda)
    if (window.scrollY < 150) {
      navLinks.forEach(link => link.classList.remove('active'));
      if (navLinks[0]) navLinks[0].classList.add('active');
    } else if (currentSectionId) {
      // Pindahkan garis biru ke menu yang sesuai dengan ID section di layar
      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentSectionId}`) {
          link.classList.add('active');
        }
      });
    }
  });
});