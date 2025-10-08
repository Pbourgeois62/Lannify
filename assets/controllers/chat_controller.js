import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['container', 'messages', 'form']
    static values = { mercureUrl: String, userId: Number, postUrl: String }

    connect() {
        // Toggle ouverture / fermeture du chat
        const chatToggle = document.getElementById('chat-toggle')
        chatToggle?.addEventListener('click', () => {
            this.containerTarget.classList.toggle('hidden')
            const isHidden = this.containerTarget.classList.contains('hidden')

            chatToggle.textContent = isHidden ? 'Ouvrir le chat' : 'Fermer le chat'
            chatToggle.classList.toggle('btn-return', !isHidden)
            chatToggle.classList.toggle('btn-secondary', isHidden)

            if (!isHidden) this.scrollToBottom()
        })

        // Mercure
        this.subscribeToMercure()

        // Envoi de message
        this.formTarget.addEventListener('submit', this.sendMessage.bind(this))
    }

    subscribeToMercure() {
        const eventSource = new EventSource(this.mercureUrlValue)
        eventSource.onmessage = event => {
            const data = JSON.parse(event.data)
            const isUser = parseInt(data.userId) === this.userIdValue

            const div = document.createElement('div')
            div.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-2`
            div.innerHTML = `
                <div class="flex items-end gap-2 ${isUser ? 'flex-row-reverse' : ''}">
                    <img src="${data.avatar}" alt="avatar" class="w-8 h-8 rounded-full shadow-sm shrink-0">
                    <div class="flex flex-col ${isUser ? 'items-end' : 'items-start'}">
                        <div class="relative px-4 py-2 rounded-2xl inline-block shadow-sm
                            ${isUser 
                                ? 'bg-gradient-to-br from-neonBlue/90 to-blue-600/80 text-darkGrey rounded-br-none self-end' 
                                : 'bg-gray-700/80 text-white rounded-bl-none self-start'}">
                            <p class="text-[13px] font-medium mb-0.5">
                                ${isUser ? 'Vous' : data.user}
                            </p>
                            <p class="text-sm leading-relaxed whitespace-normal break-all">
                                ${data.content}
                            </p>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1">${data.createdAt}</span>
                    </div>
                </div>
            `
            this.messagesTarget.appendChild(div)
            this.scrollToBottom()
        }
    }

    async sendMessage(e) {
        e.preventDefault()
        const formData = new FormData(this.formTarget)
        const response = await fetch(this.postUrlValue, {
            method: 'POST',
            body: formData
        })
        if (response.ok) this.formTarget.reset()
    }

    scrollToBottom() {
        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight
    }
}
