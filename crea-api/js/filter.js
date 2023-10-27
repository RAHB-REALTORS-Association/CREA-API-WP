document.addEventListener("DOMContentLoaded", function() {
    const itemsPerPage = 24;
    let currentPage = 1;
    const container = document.querySelector('.crea-office-container');
    const cards = Array.from(container.querySelectorAll('.crea-office-card'));
    const totalPages = Math.ceil(cards.length / itemsPerPage);
    const filterText = document.getElementById("crea-filter-text");
    const alphaButtons = document.querySelectorAll(".crea-alpha-button");
    const officeCards = document.querySelectorAll(".crea-office-card");

    // Filter by text
    filterText.addEventListener("input", function() {
        const query = this.value.toLowerCase();
        officeCards.forEach(card => {
            const name = card.querySelector("h4").textContent.toLowerCase();
            card.style.display = name.includes(query) ? "block" : "none";
        });
    });

    // Filter by first letter
    alphaButtons.forEach(button => {
        button.addEventListener("click", function() {
            const letter = this.getAttribute("data-letter").toLowerCase();
            
            // Special handling for the '#' button
            if (letter === '#') {
                officeCards.forEach(card => {
                    const name = card.querySelector("h4").textContent.toLowerCase();
                    const firstChar = name.charAt(0);
                    card.style.display = (firstChar >= '0' && firstChar <= '9') ? "block" : "none";
                });
                return; // Exit the event listener early for the '#' case
            }

            // Regular letter filtering
            officeCards.forEach(card => {
                const name = card.querySelector("h4").textContent.toLowerCase();
                card.style.display = name.startsWith(letter) ? "block" : "none";
            });
        });
    });

    // Pagination
    function updatePage() {
        cards.forEach((card, index) => {
            const shouldShow = index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage;
            card.style.display = shouldShow ? 'block' : 'none';
        });
    }

    function createPagination() {
        const pagination = document.createElement('div');
        pagination.className = 'crea-pagination';

        // First page button
        const firstPageButton = document.createElement('button');
        firstPageButton.innerText = 'First';
        firstPageButton.addEventListener('click', () => {
            currentPage = 1;
            updatePage();
        });
        pagination.appendChild(firstPageButton);

        // Previous page button
        const prevPageButton = document.createElement('button');
        prevPageButton.innerText = 'Prev';
        prevPageButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updatePage();
            }
        });
        pagination.appendChild(prevPageButton);

        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                updatePage();
            });
            pagination.appendChild(pageButton);
        }

        // Next page button
        const nextPageButton = document.createElement('button');
        nextPageButton.innerText = 'Next';
        nextPageButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                updatePage();
            }
        });
        pagination.appendChild(nextPageButton);

        // Last page button
        const lastPageButton = document.createElement('button');
        lastPageButton.innerText = 'Last';
        lastPageButton.addEventListener('click', () => {
            currentPage = totalPages;
            updatePage();
        });
        pagination.appendChild(lastPageButton);

        container.appendChild(pagination);
    }

    //updatePage();
    //createPagination();
});
