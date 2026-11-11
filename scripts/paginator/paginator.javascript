class Paginator extends Tag {
    setup() {
        this._page = 1
    }
    set number_of_pages(i) {
        this._number_of_pages = i
        this.updatePages()
    }
    get number_of_pages() {
        return this._number_of_pages
    }
    set page(i) {
        this._page = i
        this.updatePages()
    }
    get page() {
        return this._page
    }
    pageChanged(p) {
        this.page = p
        this.fire('change', p)
    }
    updatePages() {
        let pages = [];
        const currentPage = this.page; // Assume _current_page is a 1-based index
        const totalPages = this._number_of_pages;

        let startPage = 1;
        let endPage = totalPages;

        if (totalPages > 14) {
            // If the current page is among the first 12 pages
            if (currentPage <= 12) {
                endPage = 12;
            }
            // If the current page is among the last 12 pages
            else if (currentPage >= totalPages - 11) {
                startPage = totalPages - 11;
            }
            // If the current page is somewhere in the middle
            else {
                startPage = currentPage - 6;
                endPage = currentPage + 6;
            }
        }

        // First page
        pages.push({ number: 1, text: '1' });

        if (startPage > 2) {
            // "..." after the first page, to represent hidden preceding pages
            pages.push({ number: startPage - 1, text: '...' });
        }

        // Middle pages
        for (let i = Math.max(2, startPage); i <= Math.min(endPage, totalPages - 1); i++) {
            pages.push({ number: i, text: i.toString() });
        }

        if (endPage < totalPages - 1) {
            // "..." before the last page, to represent hidden succeeding pages
            pages.push({ number: endPage + 1, text: '...' });
        }

        // Last page
        if (totalPages > 1) {
            pages.push({ number: totalPages, text: totalPages.toString() });
        }
        this.setAttribute('pages', pages);
    }

}