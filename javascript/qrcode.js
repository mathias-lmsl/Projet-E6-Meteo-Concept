// Variable globale pour stocker l'id du capteur
let idCapteurCommentaire = null;
let idCarteCommentaire = null;
let type = null;

// Fonction pour basculer entre le mode sombre et clair
function toggleDarkMode() {
    document.body.classList.toggle('darkmode');
    const isDark = document.body.classList.contains('darkmode');
    localStorage.setItem('darkmode', isDark ? '1' : '0');
}

// Fonction pour garder le mode sombre après le rechargement de la page
window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('darkmode') === '1') {
        document.body.classList.add('darkmode');
    }
});





// Ouvrir la modal pour ajouter ou modifier un commentaire
// Fonction pour ouvrir le modal
function openModal(idComm = null, typeComm = null) {
    type = typeComm;
    if (type=='capteur'){
        idCapteurCommentaire = idComm; // Assigner l'id du capteur à la variable globale
        if (idCapteurCommentaire) {
        // Vous pouvez récupérer le commentaire du capteur ici via AJAX
        fetch("get_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "Id=" + idCapteurCommentaire + "&Type=" + type
        }).then(response => response.text()).then(data => {
            document.getElementById('commentText').value = data;
        });
        } else {
            document.getElementById('commentText').value = "";
        }
    }
    else if (type=='carte'){
        idCarteCommentaire = idComm; // Assigner l'id du capteur à la variable globale
        if (idCarteCommentaire) {
        // Récupérer le commentaire du capteur ici via AJAX
        fetch("get_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "Id=" + idCarteCommentaire + "&Type=" + type
        }).then(response => response.text()).then(data => {
            document.getElementById('commentText').value = data;
        });
        } else {
            document.getElementById('commentText').value = "";
        }
    }
    // Afficher le modal en modifiant son style display
    document.getElementById('commentModal').style.display = 'block';
}

// Fonction pour fermer le modal
function closeModal() {
    document.getElementById('commentModal').style.display = 'none';
    
}

// Fonction pour enregistrer le commentaire
function saveComment() {
    const comment = document.getElementById('commentText').value;

    if (type == 'capteur') {
        fetch("add-modify_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "Id=" + idCapteurCommentaire + "&Commentaire=" + comment + "&Type=" + type
        });
    } else if (type == 'carte') {
        fetch("add-modify_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "Id=" + idCarteCommentaire + "&Commentaire=" + comment + "&Type=" + type
        });
    }

    // Fermer la modal après avoir enregistré
    closeModal();
    setTimeout(() => {
        window.location.reload();
    }, 50);
}

window.addEventListener('DOMContentLoaded', () => {
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
        const [name, value] = cookie.trim().split('=');
        acc[name] = value;
        return acc;
    }, {});

    if (cookies.darkmode === '1') {
        document.body.classList.add('darkmode');
    }
});
