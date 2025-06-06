// Toggle for Tutor Provide Solutions
const tutorProvideSolutionsToggle = document.getElementById('tutor-provide-solutions-toggle');
const tutorProvideSolutionsStatus = document.getElementById('tutor-provide-solutions-status');

if (tutorProvideSolutionsToggle !== null) {
    tutorProvideSolutionsToggle.addEventListener('click', () => {
        if (tutorProvideSolutionsToggle.classList.contains('enabled')) {
            tutorProvideSolutionsToggle.classList.remove('enabled');
            tutorProvideSolutionsStatus.textContent = "Disabled";
        } else {
            tutorProvideSolutionsToggle.classList.add('enabled');
            tutorProvideSolutionsStatus.textContent = "Enabled";
        }
    });
}

// textarea behavior:
const messageTextArea = document.getElementById("message");
// 1.) submit textarea on Enter
messageTextArea.addEventListener("keypress", e => {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();

        document.getElementById('chat-form').requestSubmit();
    }
});
// 2.) increase size as user types (see also below in submit function for resetting auto-growing size after submitting to tutor)
const messageTextAreaHeightLimit = 125; /* Maximum height: 500px */
const messageTextAreaPaddingTop = parseFloat(getComputedStyle(messageTextArea)["padding-top"]);
const messageTextAreaPaddingBottom = parseFloat(getComputedStyle(messageTextArea)["padding-bottom"]);
messageTextArea.addEventListener("input", e => {
    messageTextArea.style.height = (Math.min(messageTextArea.scrollHeight, messageTextAreaHeightLimit) - messageTextAreaPaddingTop - messageTextAreaPaddingBottom ) + "px";
});


// send to tutor on submit
document.getElementById('chat-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    const messageBox = document.getElementById('message');
    const sendButton = event.target.querySelector('button');
    const chatMessages = document.getElementById('chat-messages');
    let message = messageBox.value.trim();

    // if message is empty, return and do nothing
    if (message.length === 0) {
        return;
    }

    // Disable input
    messageBox.disabled = true;
    sendButton.disabled = true;

    // add user message to messageBox
    chatMessages.innerHTML += `<div class="user-message">${message}</div>`;
    messageBox.value = '';
    messageBox.style.height = ""; // reset auto-growing size

    // show spinner
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    chatMessages.appendChild(spinner);

    // scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // add magic string to message if tutor provides solutions toggle is enabled:
    if (tutorProvideSolutionsToggle !== null && tutorProvideSolutionsToggle.classList.contains('enabled')) {
        message = "#+TPS1+#" + message; // TPS: Tutor Provides Solutions
    }

    // get tutor response
    const response = await fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    });

    const data = await response.json();

    // remove all spinners still remaining in the messageBox
    chatMessages.querySelectorAll('div.spinner').forEach((spinner) => {
        chatMessages.removeChild(spinner);
    });

    //typeWriterEffect(data.response, chatMessages);

    // for faster testing
    const botResponseDiv = document.createElement('div');
    botResponseDiv.classList.add('bot-response');
    chatMessages.appendChild(botResponseDiv);

    botResponseDiv.innerHTML = data.response;

    // Format the bot response
    formatBotResponseDiv(botResponseDiv);

    // scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Re-enable input
    messageBox.disabled = false;
    sendButton.disabled = false;
    messageBox.focus();

    // Format any new math code should it have been added by bot response
    if (window.MathJax && typeof MathJax.typeset === "function") {
        MathJax.typeset();
    }
});

/*
 * Format the bot response
 * Important:
 * - function used both for initial loading of chat history and
 *   for adding new content after each bot response
 *   --> thus perform all formatting in this function to ensure
 *       that both loading from history and live interaction look
 *       the same
 */
function formatBotResponseDiv(botResponseDiv) {
    // Apply syntax highlighting for code elements
    applySyntaxHighlighting(botResponseDiv);

    // Add copy button to pre > code blocks
    addCopyButtonToCodeBlocks(botResponseDiv);
}

// Apply syntax highlighting for code elements
function applySyntaxHighlighting(botResponseDiv) {
    botResponseDiv.querySelectorAll('code').forEach((codeBlock) => {
        hljs.highlightElement(codeBlock);
    });
}

// Add copy button to pre > code blocks
function addCopyButtonToCodeBlocks(botResponseDiv) {
    botResponseDiv.querySelectorAll('pre > code').forEach((codeBlock) => {
        if (navigator.clipboard) { // if clipboard is supported
            const copyButtonContainer = document.createElement('div');
            copyButtonContainer.classList.add('bot-response-code-copy-button-container');

            let copyButton = document.createElement("button");
            copyButton.innerText = 'Copy to Clipboard';
            copyButton.classList.add('bot-response-code-copy-button');
            copyButton.addEventListener('click', function() {
                navigator.clipboard.writeText(codeBlock.innerText).then(function () {
                    copyButton.blur();
                    copyButton.innerText = 'Copied';

                    setTimeout(function () {
                        copyButton.innerText = 'Copy to Clipboard';
                    }, 2000);
                }, function (error) {
                    copyButton.innerText = 'Error';
                })
            });

            copyButtonContainer.appendChild(copyButton);
            codeBlock.parentNode.insertBefore(copyButtonContainer, codeBlock);
        }
    });
}

function typeWriterEffect(text, chatMessages) {
    const botResponseDiv = document.createElement('div');
    botResponseDiv.classList.add('bot-response');
    chatMessages.appendChild(botResponseDiv);

    let index = 0;
    function typeWriter() {
        if (index < text.length) {
            botResponseDiv.innerHTML += text.charAt(index);
            index++;
            setTimeout(typeWriter, 1); // Adjust typing speed here
        } else {
            // Apply syntax highlighting after the typing effect is complete
            hljs.highlightElement(botResponseDiv.querySelector('code'));
        }
    }
    typeWriter();
}


