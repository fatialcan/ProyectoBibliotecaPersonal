// Referencias a los elementos del DOM
const booksContainer = document.getElementById('books-container');
const searchInput = document.getElementById('search-input');
const statusFilter = document.getElementById('status-filter');
const bookModal = document.getElementById('book-modal');
const bookForm = document.getElementById('book-form');
const btnAddBook = document.getElementById('btn-add-book');
const btnCloseModal = document.getElementById('btn-close-modal');
const btnCancelModal = document.getElementById('btn-cancel-modal');
const btnDeleteBook = document.getElementById('btn-delete-book');
const modalTitle = document.getElementById('modal-title');
const btnLogout = document.getElementById('btn-logout');
const sessionUser = document.getElementById('session-user');

// URL base para los endpoints de PHP
const API_URL = '../Controlador/';

// --- SESIÓN ---

async function verificarSesion() {
    try {
        const response = await fetch(`${API_URL}verificar_sesion.php`);
        const data = await response.json();

        if (!data.logueado) {
            window.location.href = 'login.html';
            return false;
        }

        if (sessionUser) {
            sessionUser.textContent = `Usuario: ${data.usuario}`;
        }

        return true;
    } catch (error) {
        window.location.href = 'login.html';
        return false;
    }
}

function redirigirSiNoAutorizado(response) {
    if (response.status === 401) {
        window.location.href = 'login.html';
        return true;
    }

    return false;
}

async function cerrarSesion() {
    try {
        await fetch(`${API_URL}logout.php`);
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }

    window.location.href = 'login.html';
}

// --- FUNCIONES PRINCIPALES ---

// 1. Cargar libros desde el backend (GET)
async function loadBooks() {
    const query = searchInput.value;
    const estado = statusFilter.value;

    try {
        // Petición AJAX (Fetch) sin recargar la página
        const response = await fetch(`${API_URL}buscar_libros.php?q=${encodeURIComponent(query)}&estado=${encodeURIComponent(estado)}`);

        if (redirigirSiNoAutorizado(response)) return;

        const data = await response.json();
        renderBooks(data);
    } catch (error) {
        console.error("Error al cargar los libros:", error);
        booksContainer.innerHTML = `<div class="empty-state"><p>Error al conectar con el servidor.</p></div>`;
    }
}

// 2. Renderizar las tarjetas en el HTML
function renderBooks(books) {
    booksContainer.innerHTML = ''; // Limpiamos el contenedor

    // Filtramos los que tengan borrado lógico activo
    const activeBooks = books.filter(book => !book.eliminado);

    if (activeBooks.length === 0) {
        booksContainer.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-book-open-reader"></i>
                <h2>No se encontraron libros</h2>
                <p>Intenta con otra búsqueda o agrega un nuevo libro.</p>
            </div>
        `;
        return;
    }

    activeBooks.forEach(book => {
        const isRead = book.leido;
        const statusClass = isRead ? 'status-read' : 'status-unread';
        const statusText = isRead ? 'Leído' : 'No leído';

        const card = document.createElement('div');
        card.className = 'book-card';
        card.innerHTML = `
            <div class="book-info">
                <h3>${book.titulo}</h3>
                <p class="author">${book.autor}</p>
                <p class="genre">${book.genero}</p>
                <p class="notes">${book.notas || 'Sin notas.'}</p>
            </div>
            <div>
                <div class="book-status ${statusClass}">
                    <span class="status-badge"></span> ${statusText}
                </div>
                <div class="card-actions">
                    <button class="btn btn-secondary toggle-read-btn" data-id="${book.id}" data-leido="${isRead}">
                        <i class="fa-solid ${isRead ? 'fa-book-open' : 'fa-bookmark'}"></i> ${isRead ? 'Marcar No Leído' : 'Marcar Leído'}
                    </button>
                    <button class="btn btn-secondary edit-btn" data-id="${book.id}">
                        <i class="fa-solid fa-pen"></i> Editar
                    </button>
                </div>
            </div>
        `;
        booksContainer.appendChild(card);
    });

    attachCardEvents();
}

// 3. Guardar o Editar un libro (POST / PUT)
async function saveBook(e) {
    e.preventDefault();

    const id = document.getElementById('book-id').value;
    const bookData = {
        titulo: document.getElementById('form-title').value,
        autor: document.getElementById('form-author').value,
        genero: document.getElementById('form-genre').value,
        notas: document.getElementById('form-notes').value,
        leido: document.getElementById('form-read').checked
    };

    try {
        let response;
        if (id) {
            // Si hay ID, es una EDICIÓN (PUT)
            bookData.id = parseInt(id);
            response = await fetch(`${API_URL}editar_libro.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookData)
            });
        } else {
            // Si no hay ID, es un NUEVO LIBRO (POST)
            response = await fetch(`${API_URL}agregar_libro.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookData)
            });
        }

        if (redirigirSiNoAutorizado(response)) return;

        if (response.ok) {
            closeModal();
            loadBooks(); // Recargamos la lista actualizada
        }
    } catch (error) {
        console.error("Error al guardar:", error);
    }
}

// 4. Cambiar estado de lectura rápidamente (POST toggle)
async function toggleReadStatus(id, currentStatus) {
    try {
        const response = await fetch(`${API_URL}leido_libro.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(id), leido: !currentStatus })
        });

        if (redirigirSiNoAutorizado(response)) return;

        loadBooks(); // Refrescamos para ver el cambio
    } catch (error) {
        console.error("Error al cambiar estado:", error);
    }
}

// --- UTILIDADES DEL MODAL Y EVENTOS ---

function openModal(isEdit = false, bookData = null) {
    bookModal.classList.add('active');

    if (isEdit && bookData) {
        modalTitle.textContent = 'Editar Libro';
        document.getElementById('book-id').value = bookData.id;
        document.getElementById('form-title').value = bookData.titulo;
        document.getElementById('form-author').value = bookData.autor;
        document.getElementById('form-genre').value = bookData.genero;
        document.getElementById('form-notes').value = bookData.notas || '';
        document.getElementById('form-read').checked = bookData.leido;
        btnDeleteBook.style.display = 'block'; // Mostrar botón eliminar
    } else {
        modalTitle.textContent = 'Agregar Nuevo Libro';
        bookForm.reset();
        document.getElementById('book-id').value = '';
        btnDeleteBook.style.display = 'none'; // Ocultar botón eliminar
    }
}

function closeModal() {
    bookModal.classList.remove('active');
    bookForm.reset();
}

// Asignar eventos a los botones dentro de las tarjetas dinámicas
function attachCardEvents() {
    document.querySelectorAll('.toggle-read-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.dataset.id;
            const currentStatus = e.currentTarget.dataset.leido === 'true';
            toggleReadStatus(id, currentStatus);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = e.currentTarget.dataset.id;
            // Para editar, primero buscamos los detalles de este libro específico
            // En un caso real podrías traer la data del array local, aquí consultamos de nuevo por simplicidad
            const response = await fetch(`${API_URL}buscar_libros.php`);

            if (redirigirSiNoAutorizado(response)) return;

            const books = await response.json();
            const bookToEdit = books.find(b => b.id == id);

            if (bookToEdit) {
                openModal(true, bookToEdit);
            }
        });
    });
}

// Activar el Borrado Lógico
async function deleteBook() {
    if (confirm("¿Estás seguro de que deseas eliminar este libro de tu catálogo?")) {
        const id = document.getElementById('book-id').value;
        try {
            const response = await fetch(`${API_URL}borrar_libro.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(id) })
            });

            if (redirigirSiNoAutorizado(response)) return;

            if (response.ok) {
                closeModal();
                loadBooks(); // Recarga la lista para que el libro desaparezca
            }
        } catch (error) {
            console.error("Error al eliminar:", error);
        }
    }
}

// --- EVENT LISTENERS GLOBALES ---

async function iniciarApp() {
    const sesionActiva = await verificarSesion();
    if (!sesionActiva) return;

    // Busqueda en tiempo real (evento 'input' detecta cada tecla presionada y loadBooks la funcion a realizar)
    searchInput.addEventListener('input', loadBooks);
    statusFilter.addEventListener('change', loadBooks);

    // Controles del Modal
    btnAddBook.addEventListener('click', () => openModal(false));
    btnCloseModal.addEventListener('click', closeModal);
    btnCancelModal.addEventListener('click', closeModal);
    bookForm.addEventListener('submit', saveBook);
    btnDeleteBook.addEventListener('click', deleteBook);

    if (btnLogout) {
        btnLogout.addEventListener('click', cerrarSesion);
    }

    // Cargar libros al iniciar la página
    loadBooks();
}

document.addEventListener('DOMContentLoaded', iniciarApp);
