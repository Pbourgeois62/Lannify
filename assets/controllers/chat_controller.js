import { Controller } from "@hotwired/stimulus";

/**
 * Contrôleur Stimulus pour le chat en temps réel (Mercure)
 */
export default class extends Controller {
    static targets = ["messages", "form", "input", "toggle", "box", "iconOpen", "iconClose"];
    static values = {
        mercureUrl: String,
        currentUserId: Number,
        eventId: Number
    };

    connect() {
        if (!this.mercureUrlValue) {
            console.error("🔴 Mercure URL non définie !");
            return;
        }

        this.subscribe();
    }

    // --- 🔔 Souscription Mercure ---
    subscribe() {
        const es = new EventSource(this.mercureUrlValue);

        es.onopen = () => console.log("🟢 Connexion Mercure ouverte");
        es.onerror = (err) => console.error("🔴 Erreur EventSource:", err);

        es.onmessage = (e) => {
            try {
                const msg = JSON.parse(e.data);
                const isCurrentUser = Number(msg.userId) === this.currentUserIdValue;
                this.addMessage(msg, isCurrentUser);
            } catch (err) {
                console.error("❌ Erreur parsing Mercure:", err);
            }
        };

        this.eventSource = es;
    }

    // --- 💬 Ajout d’un message dans le DOM ---
    addMessage(msg, isCurrentUser) {
        const wrapper = document.createElement("div");
        wrapper.className = `flex items-end gap-2 ${isCurrentUser ? "justify-end" : "justify-start"}`;

        // --- Avatar ---
        const avatar = document.createElement("img");
        avatar.src = msg.avatar || "/images/default-avatar.webp";
        avatar.alt = `Avatar ${msg.user}`;
        avatar.className = "w-8 h-8 object-cover rounded-full border border-gray-600";

        if (!isCurrentUser) wrapper.appendChild(avatar);

        // --- Bulle de message ---
        const bubble = document.createElement("div");
        bubble.className = `
            flex flex-col p-2 px-3 max-w-[75%] shadow-md
            ${isCurrentUser
                ? "bg-neonBlue text-black rounded-tl-lg rounded-tr-lg rounded-br-lg"
                : "bg-gray-700 text-white rounded-tl-lg rounded-tr-lg rounded-bl-lg"}
        `;

        // Nom de l’expéditeur (si autre utilisateur)
        if (!isCurrentUser) {
            const user = document.createElement("span");
            user.className = "font-semibold text-xs text-neonBlue mb-0.5";
            user.textContent = msg.user;
            bubble.appendChild(user);
        }

        // Contenu
        const content = document.createElement("div");
        content.textContent = msg.content;
        bubble.appendChild(content);

        // Heure
        const time = document.createElement("span");
        time.className = "text-[0.7rem] text-right mt-1 opacity-70";
        time.textContent = msg.createdAt;
        bubble.appendChild(time);

        wrapper.appendChild(bubble);

        // Avatar utilisateur courant (à droite)
        if (isCurrentUser) wrapper.appendChild(avatar.cloneNode(true));

        // --- Insertion dans le DOM ---
        this.messagesTarget.appendChild(wrapper);

        // Scroll automatique vers le bas
        this.messagesTarget.scrollTo({
            top: this.messagesTarget.scrollHeight,
            behavior: "smooth"
        });
    }

    // --- 🟢 Affichage / masquage du chat ---
    toggle() {
        this.boxTarget.classList.toggle("hidden");
        this.toggleTarget.classList.toggle("rounded-b-none");
        this.toggleTarget.classList.toggle("rounded-b-lg");

        // Focus auto dans le champ texte à l’ouverture
        if (!this.boxTarget.classList.contains("hidden")) {
            setTimeout(() => this.inputTarget.focus(), 100);
        }
    }

    // --- ✉️ Envoi d’un message ---
    async send(e) {
        e.preventDefault();

        const content = this.inputTarget.value.trim();
        if (!content) return;

        try {
            await fetch(`/event/${this.eventIdValue}/chat`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ content })
            });
        } catch (err) {
            console.error("🔴 Erreur lors de l’envoi du message:", err);
        }

        this.inputTarget.value = "";
    }

    // --- 🚪 Nettoyage ---
    disconnect() {
        if (this.eventSource) this.eventSource.close();
    }
}
