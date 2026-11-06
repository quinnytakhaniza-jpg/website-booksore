const booksData = {
  teen: [
    { title: "Hujan", author: "Tere Liye", price: "Rp 89.000", cover: "https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=300&q=80" },
    { title: "Bumi", author: "Tere Liye", price: "Rp 95.000", cover: "https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=300&q=80" },
    { title: "Bulan", author: "Tere Liye", price: "Rp 95.000", cover: "https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80" }
  ],
  adult: [
    { title: "Laut Bercerita", author: "Leila S. Chudori", price: "Rp 115.000", cover: "https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80" },
    { title: "Cantik Itu Luka", author: "Eka Kurniawan", price: "Rp 120.000", cover: "https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=300&q=80" }
  ],
  selfhelp: [
    { title: "Atomic Habits", author: "James Clear", price: "Rp 108.000", cover: "https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=300&q=80" },
    { title: "Filosofi Teras", author: "Henry Manampiring", price: "Rp 98.000", cover: "https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=300&q=80" }
  ],
  business: [
    { title: "Psychology of Money", author: "Morgan Housel", price: "Rp 95.000", cover: "https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&w=300&q=80" },
    { title: "Rich Dad Poor Dad", author: "Robert T. Kiyosaki", price: "Rp 88.000", cover: "https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=300&q=80" }
  ],
  romance: [
    { title: "It Ends With Us", author: "Colleen Hoover", price: "Rp 125.000", cover: "https://images.unsplash.com/photo-1476275466078-4007374efbbe?auto=format&fit=crop&w=300&q=80" },
    { title: "Satu Hari di 2018", author: "Boy Candra", price: "Rp 75.000", cover: "https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=300&q=80" }
  ]
};

const categoryNames = {
  teen: "Teen & Young Adult",
  adult: "Adult Fiction",
  selfhelp: "Motivation & Self-Help",
  business: "Business & Investing",
  romance: "Romance"
};

document.addEventListener("DOMContentLoaded", () => {
  if (window.lucide) {
    lucide.createIcons();
  }

  const modal = document.getElementById("bookModal");
  const closeModalBtn = document.getElementById("closeModal");
  const modalTitle = document.getElementById("modal-category-title");
  const modalContainer = document.getElementById("modal-books-container");

  // Ketika kartu kategori diklik, buka pop-up (modal)
  const cards = document.querySelectorAll(".category-card");
  cards.forEach((card) => {
    card.addEventListener("click", () => {
      const categoryKey = card.getAttribute("data-category");
      const books = booksData[categoryKey] || [];

      modalTitle.innerText = `Kategori: ${categoryNames[categoryKey]}`;
      modalContainer.innerHTML = "";

      books.forEach((book) => {
        const bookCard = `
          <div class="book-card">
            <div>
              <img src="${book.cover}" alt="${book.title}" class="book-cover" />
              <h4 class="book-title">${book.title}</h4>
              <p class="book-author">${book.author}</p>
            </div>
            <div>
              <p class="book-price">${book.price}</p>
              <div class="action-buttons">
                <button class="btn btn-cart" onclick="addToCart('${book.title}')">+ Keranjang</button>
                <button class="btn btn-buy" onclick="buyNow('${book.title}')">Beli</button>
              </div>
            </div>
          </div>
        `;
        modalContainer.innerHTML += bookCard;
      });

      modal.classList.add("active");
    });
  });

  // Tutup modal via tombol 'X'
  closeModalBtn.addEventListener("click", () => {
    modal.classList.remove("active");
  });

  // Tutup modal jika klik di luar kotak putih
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});

function addToCart(title) {
  alert(`"${title}" berhasil ditambahkan ke keranjang!`);
}

function buyNow(title) {
  alert(`Melanjutkan ke pembayaran untuk "${title}"...`);
}