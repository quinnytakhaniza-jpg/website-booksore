lucide.createIcons();

function openCategory(key) {
  const category = categoriesData[key];
  if (!category) return;

  document.getElementById('explore-section').classList.add('hidden');
  document.getElementById('category-detail-section').classList.remove('hidden');

  document.getElementById('banner-title').innerText = category.title;
  document.getElementById('banner-desc').innerText = category.desc;
  document.getElementById('banner-count').innerHTML = `<i data-lucide="book-open"></i> ${category.count} buku tersedia`;

  const grid = document.getElementById('book-grid');
  grid.innerHTML = ''; 

  category.books.forEach(book => {
    const bookHTML = `
      <div class="book-card">
        <div>
          <div class="book-cover-wrapper">
            <img src="${book.cover}" alt="${book.title}" class="book-cover">
          </div>
          <div class="book-title">${book.title}</div>
          <div class="book-author">${book.author}</div>
          <div class="book-rating">★ ${book.rating}</div>
        </div>
        <div class="book-footer">
          <span class="book-price">${book.price}</span>
          <button class="cart-btn"><i data-lucide="shopping-cart"></i></button>
        </div>
      </div>
    `;
    grid.innerHTML += bookHTML;
  });

  lucide.createIcons();
  window.scrollTo(0, 0);
}

function goBack() {
  document.getElementById('category-detail-section').classList.add('hidden');
  document.getElementById('explore-section').classList.remove('hidden');
}

//button//
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab-btn');
  const books = document.querySelectorAll('.book-card');

  tabs.forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();

      // 1. Ubah tombol yang aktif
      tabs.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');

      // 2. Ambil kategori yang diklik
      const selectedCategory = this.getAttribute('data-category');

      // 3. Filter buku
      books.forEach(book => {
        const bookCategory = book.getAttribute('data-category');

        if (selectedCategory === 'all' || bookCategory === selectedCategory) {
          book.classList.remove('hidden');
        } else {
          book.classList.add('hidden');
        }
      });
    });
  });
});