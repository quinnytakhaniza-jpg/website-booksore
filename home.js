document.addEventListener('DOMContentLoaded', function () {
  const tabButtons = document.querySelectorAll('.tab-btn');
  const bookCards = document.querySelectorAll('.book-card');
  const searchInput = document.getElementById('bookSearchInput');

  let currentCategory = 'popular';

  function filterBooks() {
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

    bookCards.forEach(card => {
      const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
      const titleElement = card.querySelector('h3');
      const cardTitle = titleElement ? titleElement.textContent.toLowerCase() : '';

      let matchesCategory = false;
      
      if (currentCategory === 'popular' || currentCategory === 'semua' || currentCategory === 'all') {
        matchesCategory = cardCategory.includes('popular');
      } else {
        // Memastikan pencocokan kategori spesifik (seperti romance, fiksi, dll) berjalan akurat
        matchesCategory = cardCategory.includes(currentCategory);
      }

      const matchesSearch = cardTitle.includes(searchTerm);

      if (matchesCategory && matchesSearch) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  tabButtons.forEach(button => {
    button.addEventListener('click', function () {
      tabButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');

      currentCategory = this.getAttribute('data-category').toLowerCase();
      filterBooks();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', filterBooks);
  }

  // Close Banner
  const promoBanner = document.getElementById('promoBanner');
  const closeBannerBtn = document.getElementById('closeBannerBtn');
  if (closeBannerBtn && promoBanner) {
    closeBannerBtn.addEventListener('click', function () {
      promoBanner.style.display = 'none';
    });
  }

  // Newsletter
  const newsletterForm = document.getElementById('newsletterForm');
  const subscribeAlert = document.getElementById('subscribeAlert');
  const closeAlertBtn = document.getElementById('closeAlertBtn');
  if (newsletterForm && subscribeAlert) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      subscribeAlert.classList.remove('hidden');
      newsletterForm.reset();
    });
  }
  if (closeAlertBtn && subscribeAlert) {
    closeAlertBtn.addEventListener('click', function () {
      subscribeAlert.classList.add('hidden');
    });
  }
});