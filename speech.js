/**
 * Native Browser Speech-to-Text Integration
 * Uses Web Speech API (webkitSpeechRecognition)
 */

class SpeechDictation {
    constructor() {
        this.recognition = null;
        this.isRecording = false;
        this.targetInputId = null;
        this.onStateChange = null; // Callback for UI updates
        
        this.init();
    }

    init() {
        // Check for browser support
        window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        
        if (!window.SpeechRecognition) {
            console.warn("Speech Recognition API is not supported in this browser.");
            return;
        }

        this.recognition = new window.SpeechRecognition();
        this.recognition.continuous = true;
        this.recognition.interimResults = true; // Show words as they are spoken
        this.recognition.lang = 'en-US'; // Default

        // Event listeners
        this.recognition.onstart = () => {
            this.isRecording = true;
            if (this.onStateChange) this.onStateChange('recording');
        };

        this.recognition.onresult = (event) => {
            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }

            this.updateInput(finalTranscript, interimTranscript);
        };

        this.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            if (this.onStateChange) this.onStateChange('error', event.error);
            this.stop();
        };

        this.recognition.onend = () => {
            // Auto-restart if we didn't explicitly stop (e.g. pause in speaking)
            if (this.isRecording) {
                try {
                    this.recognition.start();
                } catch(e) {
                    this.isRecording = false;
                    if (this.onStateChange) this.onStateChange('stopped');
                }
            } else {
                if (this.onStateChange) this.onStateChange('stopped');
            }
        };
    }

    setLanguage(langCode) {
        if (this.recognition) {
            this.recognition.lang = langCode;
            // If already recording, restart to apply new language
            if (this.isRecording) {
                this.stop();
                setTimeout(() => this.start(this.targetInputId), 300);
            }
        }
    }

    start(inputId, stateCallback) {
        if (!this.recognition) {
            alert("Your browser does not support voice dictation. Please use Chrome or Edge.");
            return;
        }

        this.targetInputId = inputId;
        if (stateCallback) this.onStateChange = stateCallback;

        if (this.isRecording) {
            this.stop();
            return;
        }

        try {
            this.recognition.start();
        } catch(e) {
            console.error("Could not start recognition:", e);
        }
    }

    stop() {
        if (this.recognition && this.isRecording) {
            this.isRecording = false;
            this.recognition.stop();
            // Clear interim text visual from UI if needed
            if (this.onStateChange) this.onStateChange('stopped');
        }
    }

    updateInput(finalText, interimText) {
        if (!this.targetInputId) return;
        
        const inputEl = document.getElementById(this.targetInputId);
        if (!inputEl) return;

        // Command mapping (Bonus feature)
        let processedFinal = this.processCommands(finalText);

        if (processedFinal) {
            // Append final text
            let currentVal = inputEl.value;
            // Add space if needed
            if (currentVal.length > 0 && !currentVal.endsWith(' ') && !currentVal.endsWith('\n')) {
                currentVal += ' ';
            }
            // Capitalize first letter of new sentence
            if (currentVal.length === 0 || currentVal.endsWith('. ') || currentVal.endsWith('! ') || currentVal.endsWith('\n')) {
                processedFinal = processedFinal.charAt(0).toUpperCase() + processedFinal.slice(1);
            }
            
            inputEl.value = currentVal + processedFinal;
            
            // Trigger input event to resize textarea or trigger validations
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // We can pass interimText to the UI to show a "listening..." preview
        if (this.onStateChange) {
            this.onStateChange('interim', interimText);
        }
    }

    processCommands(text) {
        if (!text) return text;
        let t = text;
        
        // Basic punctuation mappings (EN/FR) case-insensitive
        t = t.replace(/ point d'interrogation/gi, '?');
        t = t.replace(/ question mark/gi, '?');
        
        t = t.replace(/ point d'exclamation/gi, '!');
        t = t.replace(/ exclamation mark/gi, '!');
        
        t = t.replace(/ nouvelle ligne/gi, '\n');
        t = t.replace(/ new line/gi, '\n');
        
        t = t.replace(/ virgule/gi, ',');
        t = t.replace(/ comma/gi, ',');

        t = t.replace(/ point/gi, '.');
        t = t.replace(/ period/gi, '.');

        return t; 
    }
}

// Global instance
window.SmartDictation = new SpeechDictation();

// UI Helper Function
function toggleDictation(inputId, btnElement) {
    if(!window.SpeechRecognition && !window.webkitSpeechRecognition) {
        alert("Voice typing is not supported in your browser.");
        return;
    }

    const isRecording = window.SmartDictation.isRecording;
    
    // Find the associated lang select and interim display
    const wrapper = btnElement.closest('.dictation-wrapper');
    const langSelect = wrapper ? wrapper.querySelector('.dictation-lang') : null;
    const interimDisplay = wrapper ? wrapper.querySelector('.dictation-interim') : null;

    if (langSelect && !isRecording) {
        window.SmartDictation.setLanguage(langSelect.value);
    }

    window.SmartDictation.start(inputId, (state, data) => {
        if (state === 'recording') {
            btnElement.classList.add('recording');
            btnElement.innerHTML = '<i class="fa-solid fa-microphone-lines fa-beat-fade"></i>';
            if(interimDisplay) interimDisplay.style.display = 'block';
        } 
        else if (state === 'stopped' || state === 'error') {
            btnElement.classList.remove('recording');
            btnElement.innerHTML = '<i class="fa-solid fa-microphone"></i>';
            if(interimDisplay) {
                interimDisplay.style.display = 'none';
                interimDisplay.innerText = '';
            }
        }
        else if (state === 'interim') {
            if(interimDisplay) {
                interimDisplay.innerText = data ? `...${data}` : 'Écoute en cours...';
            }
        }
    });
}
