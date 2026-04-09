const CHANNEL_NAME = 'restaurant-pos.operations';
const EVENT_NAME = '.operations.updated';

const emitLivewireRefresh = (payload) => {
    window.dispatchEvent(
        new CustomEvent('restaurant-pos:operations-updated', {
            detail: payload,
        })
    );

    if (window.Livewire?.dispatch) {
        window.Livewire.dispatch('operations-updated', payload);
    }
};

const bootRealtime = () => {
    if (window.__restaurantPosRealtimeBooted) {
        return;
    }

    if (document.body?.dataset.realtime !== 'enabled' || !window.Echo) {
        return;
    }

    window.__restaurantPosRealtimeBooted = true;

    window.Echo.channel(CHANNEL_NAME).listen(EVENT_NAME, (payload) => {
        emitLivewireRefresh(payload);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootRealtime, { once: true });
} else {
    bootRealtime();
}

document.addEventListener('livewire:initialized', bootRealtime);
