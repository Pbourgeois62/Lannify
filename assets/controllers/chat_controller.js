import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = [ "messages", "form", "input", "toggle", "box" ]
    static values = { eventId: Number, currentUserId: Number, mercureUrl: String }

    connect() {
        console.log("🔵 ChatController connecté");
        this.subscribe();
    }

    subscribe() {
        const url = new URL(this.mercureUrlValue);
        url.searchParams.append('topic', `/event/${this.eventIdValue}/chat`);

        const es = new EventSource(url);

        es.onopen = () => console.log("🟢 Connexion Mercure ouverte");
        es.onerror = err => console.error("🔴 Erreur EventSource:", err);

        es.onmessage = e => {
            try {
                const msg = JSON.parse(e.data);
                const isCurrentUser = Number(msg.userId) === this.currentUserIdValue;
                this.addMessage(msg, isCurrentUser);
            } catch (err) {
                console.error("❌ Erreur parsing Mercure:", err);
            }
        };
    }

    addMessage(msg, isCurrentUser) {
        const wrapper = document.createElement("div");
        wrapper.className = `flex items-end gap-2 ${isCurrentUser ? "justify-end" : "justify-start"}`;

        // Avatar
        if (!isCurrentUser) {
            wrapper.appendChild(this.createAvatar(msg.avatar));
        }

        // Bulle
        const bubble = document.createElement("div");
        bubble.className = `p-3 max-w-[80%] ${isCurrentUser
            ? "bg-neonBlue text-black rounded-tl-lg rounded-tr-lg rounded-bl-lg rounded-br-none"
            : "bg-gray-700 text-white rounded-tl-lg rounded-tr-lg rounded-br-lg rounded-bl-none"
        }`;

        if (!isCurrentUser) {
            const user = document.createElement("span");
            user.className = "block font-bold text-sm text-neonBlue";
            user.textContent = msg.user;
            bubble.appendChild(user);
        }

        const content = document.createElement("div");
        content.textContent = msg.content;

        const time = document.createElement("span");
        time.className = `block text-xs mt-1 ${isCurrentUser ? "text-gray-800" : "text-gray-400"}`;
        time.textContent = msg.createdAt;

        bubble.append(content, time);
        wrapper.appendChild(bubble);

        if (isCurrentUser) {
            wrapper.appendChild(this.createAvatar(msg.avatar));
        }

        this.messagesTarget.appendChild(wrapper);
        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
    }

    createAvatar(url) {
        const avatar = document.createElement("img");
        avatar.src = url || "/images/default-avatar.png";
        avatar.alt = "avatar";
        avatar.className = "w-8 h-8 rounded-full";
        return avatar;
    }

    toggle() {
        this.boxTarget.classList.toggle("hidden");
    }

    async send(event) {
        event.preventDefault();
        const content = this.inputTarget.value.trim();
        if (!content) return;

        try {
            const response = await fetch(`/event/${this.eventIdValue}/chat`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ content })
            });
            console.log("✅ Message envoyé:", response.status);
        } catch (err) {
            console.error("🔴 Erreur fetch:", err);
        }

        this.inputTarget.value = "";
    }
}
