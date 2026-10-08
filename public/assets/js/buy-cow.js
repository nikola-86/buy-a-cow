const buyCowButton = document.getElementById('buy-cow-button');
const thankYouMessage = document.getElementById('buy-cow-thank-you');
const errorMessage = document.getElementById('buy-cow-error');

function postBuyCowEvent() {
    return fetch('/api/events', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': buyCowButton.dataset.csrfToken,
        },
        body: JSON.stringify({ target: buyCowButton.dataset.eventTarget }),
    });
}

buyCowButton.addEventListener('click', async () => {
    buyCowButton.disabled = true;
    errorMessage.hidden = true;

    try {
        const response = await postBuyCowEvent();

        if (response.status === 401) {
            window.location.assign('/login');
            return;
        }

        if (response.status !== 201) {
            throw new Error(`Unexpected response status: ${response.status}`);
        }

        buyCowButton.hidden = true;
        thankYouMessage.hidden = false;
    } catch (error) {
        console.error(error);
        buyCowButton.disabled = false;
        errorMessage.hidden = false;
    }
});
