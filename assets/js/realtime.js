(() => {
    const isProductionSafeBrgyHost = (hostname = '') => hostname === 'safebrgy.com' || hostname.endsWith('.safebrgy.com');

    const getApiUrl = () => {
        const pathname = window.location.pathname || '/';
        const relativeRoot = /^\/safebrgy(?:\/|$)/.test(pathname) ? '/safebrgy' : '';
        const apiUrl = `${relativeRoot}/api/fetch.php`;

        if (isProductionSafeBrgyHost(window.location.hostname)) {
            return apiUrl;
        }

        return `${window.location.origin}${apiUrl}`;
    };

    const getNotificationCountUrl = () => {
        const pathname = window.location.pathname || '/';
        const relativeRoot = /^\/safebrgy(?:\/|$)/.test(pathname) ? '/safebrgy' : '';
        const apiUrl = `${relativeRoot}/api/notification-count.php`;

        if (isProductionSafeBrgyHost(window.location.hostname)) {
            return apiUrl;
        }

        return `${window.location.origin}${apiUrl}`;
    };

    const escapeSelector = (value) => {
        if (!value) {
            return '';
        }

        return value.replace(/([ #;?%&,.+~':"!^$[\]=>|\\/@*`{}()])/g, '\\$1');
    };

    let lastNotificationId = 0;
    let polling = false;
    let tableRefreshInProgress = false;

    const liveRegionSelectors = [
        '#reportsTable',
        '#adminRequestsTable',
        '#unverifiedTable',
        '#verifiedTable',
        '#requests-table-body',
        '#announcementCards'
    ];

    function updateBrowserTitle(count) {
        const baseTitle = 'SafeBrgy';
        const safeCount = Number(count) || 0;
        document.title = safeCount > 0 ? `(${safeCount}) ${baseTitle}` : baseTitle;
    }

    async function updateNotificationCount() {
        try {
            const response = await fetch(getNotificationCountUrl(), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (!data || !data.success) {
                return;
            }

            const badges = data.badges && typeof data.badges === 'object' ? data.badges : {};
            Object.keys(badges).forEach((target) => {
                updateNavigationBadge(target, badges[target]);
            });

            Object.keys({ dashboard: 1, announcement: 1, reports: 1, requests: 1, verification: 1 }).forEach((target) => {
                if (!Object.prototype.hasOwnProperty.call(badges, target)) {
                    updateNavigationBadge(target, 0);
                }
            });

            updateBrowserTitle(data.total ?? data.count ?? 0);
        } catch (error) {
            console.warn('Notification count update failed:', error);
        }
    }

    function updateNavigationBadge(target, count) {
        if (!target) {
            return;
        }

        const badges = document.querySelectorAll(`[data-notification-badge="${escapeSelector(target)}"]`);
        const value = Number(count) || 0;

        badges.forEach((badge) => {
            let countNode = badge.querySelector('.notification-count');

            if (value > 0) {
                if (!countNode) {
                    countNode = document.createElement('span');
                    countNode.className = 'notification-count';
                    badge.appendChild(countNode);
                }

                countNode.textContent = String(value);
                countNode.classList.remove('d-none');
            } else if (countNode) {
                countNode.textContent = '0';
                countNode.classList.add('d-none');
            }
        });
    }

    function clearNavigationBadge(target) {
        if (!target) {
            return;
        }

        document.querySelectorAll(`[data-notification-badge="${escapeSelector(target)}"]`).forEach((badge) => {
            const countNode = badge.querySelector('.notification-count');
            if (countNode) {
                countNode.textContent = '0';
                countNode.classList.add('d-none');
            }
        });
    }

    function updateMobileAggregate(total) {
        const badge = document.querySelector('#mobileMenuButton .notification-badge');
        if (!badge) {
            return;
        }

        if (total > 0) {
            badge.textContent = String(total);
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '';
            badge.classList.add('d-none');
        }
    }

    function syncBadges(data) {
        const badges = data && typeof data.badges === 'object' ? data.badges : {};
        let aggregate = 0;

        Object.keys(badges).forEach((target) => {
            const count = Number(badges[target]) || 0;
            aggregate += count;
            updateNavigationBadge(target, count);
        });

        document.querySelectorAll('[data-notification-badge]').forEach((element) => {
            const target = element.getAttribute('data-notification-badge');
            if (!Object.prototype.hasOwnProperty.call(badges, target)) {
                clearNavigationBadge(target);
            }
        });

        updateMobileAggregate(aggregate);
    }

    async function markNotificationTypeRead(target) {
        if (!target) {
            return;
        }

        const url = `${getApiUrl()}?action=read&target=${encodeURIComponent(target)}`;
        try {
            await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json'
                }
            });
            updateNotificationCount();
        } catch (error) {
            console.warn('Could not clear notification badge', error);
        }
    }

    async function markNotificationEntityRead(entityType, entityId, trigger) {
        if (!entityType || !entityId) {
            return;
        }

        const url = `${getApiUrl()}?action=read_entity&entity_type=${encodeURIComponent(entityType)}&entity_id=${encodeURIComponent(entityId)}`;
        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return;
            }

            const row = trigger.closest('tr, .announcement-card');
            row?.querySelector('.notification-dot')?.remove();
        } catch (error) {
            console.warn('Could not mark notification as read', error);
        }
    }

    async function refreshLiveRegions() {
        if (tableRefreshInProgress || document.querySelector('.modal.show')) {
            return;
        }

        const currentRegions = liveRegionSelectors
            .map((selector) => ({ selector, element: document.querySelector(selector) }))
            .filter((region) => region.element);
        if (!currentRegions.length) {
            return;
        }

        tableRefreshInProgress = true;
        try {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) {
                return;
            }

            const html = await response.text();
            const refreshedPage = new DOMParser().parseFromString(html, 'text/html');
            let requestRowsChanged = false;
            let regionsChanged = false;

            currentRegions.forEach(({ selector, element }) => {
                const refreshedElement = refreshedPage.querySelector(selector);
                if (!refreshedElement || element.innerHTML === refreshedElement.innerHTML) {
                    return;
                }

                element.replaceChildren(...Array.from(refreshedElement.childNodes, (node) => document.importNode(node, true)));
                regionsChanged = true;
                if (selector === '#adminRequestsTable') {
                    requestRowsChanged = true;
                }
            });

            if (requestRowsChanged) {
                document.querySelectorAll('[id^="viewRequestModal"]').forEach((modal) => modal.remove());
                refreshedPage.querySelectorAll('[id^="viewRequestModal"]').forEach((modal) => {
                    document.body.appendChild(document.importNode(modal, true));
                });
            }

            if (regionsChanged) {
                document.dispatchEvent(new Event('safebrgy:live-refresh'));
            }
        } catch (error) {
            console.warn('Live table refresh failed:', error);
        } finally {
            tableRefreshInProgress = false;
        }
    }

    function handleNotification(notification) {
        if (!notification || !notification.title) {
            return;
        }

        if (typeof window.showSafeBrgyNotification === 'function') {
            window.showSafeBrgyNotification(notification);
        }
    }

    async function checkUpdates() {
        if (polling) {
            return;
        }

        polling = true;

        try {
            const url = `${getApiUrl()}?last_id=${encodeURIComponent(lastNotificationId)}`;
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Notification fetch failed: ${response.status}`);
            }

            const data = await response.json();
            if (!data || !data.success) {
                return;
            }

            if (Array.isArray(data.notifications) && data.notifications.length > 0) {
                const latestId = data.notifications.reduce((max, item) => {
                    const id = Number(item && item.id ? item.id : 0);
                    return Math.max(max, id);
                }, lastNotificationId);

                if (latestId > lastNotificationId) {
                    lastNotificationId = latestId;
                }

                data.notifications.forEach((notification) => {
                    handleNotification(notification);
                });
            }

            if (data.badges) {
                syncBadges(data);
            }
        } catch (error) {
            console.warn('Background notification poll failed:', error);
        } finally {
            polling = false;
        }
    }

    function initialize() {
        const initialUrl = `${getApiUrl()}?last_id=0`;

        fetch(initialUrl, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json'
            }
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (!data || !data.success) {
                    return;
                }

                if (Array.isArray(data.notifications) && data.notifications.length > 0) {
                    lastNotificationId = data.notifications.reduce((max, item) => Math.max(max, Number(item.id || 0)), 0);
                    const latestUnread = data.notifications
                        .filter((notification) => Number(notification.is_read) === 0)
                        .at(-1);
                    if (latestUnread) {
                        handleNotification(latestUnread);
                    }
                }

                if (data.badges) {
                    syncBadges(data);
                }
            })
            .catch(() => {
                // Ignore missing session or blocked requests; they are expected on public pages.
            });

        updateNotificationCount();

        document.addEventListener('click', (event) => {
            const entityTrigger = event.target.closest('[data-notification-entity][data-notification-id]');
            if (entityTrigger && !entityTrigger.matches('[data-bs-toggle="modal"]')) {
                markNotificationEntityRead(
                    entityTrigger.getAttribute('data-notification-entity'),
                    entityTrigger.getAttribute('data-notification-id'),
                    entityTrigger
                );
            }

            const link = event.target.closest('[data-notification-badge]');
            if (!link) {
                return;
            }

            const target = link.getAttribute('data-notification-badge');
            if (!target) {
                return;
            }

            clearNavigationBadge(target);
            markNotificationTypeRead(target);
        });

        document.addEventListener('show.bs.modal', (event) => {
            const entityTrigger = event.relatedTarget?.closest('[data-notification-entity][data-notification-id]');
            if (entityTrigger) {
                markNotificationEntityRead(
                    entityTrigger.getAttribute('data-notification-entity'),
                    entityTrigger.getAttribute('data-notification-id'),
                    entityTrigger
                );
            }
        });

        window.setInterval(checkUpdates, 3000);
        window.setInterval(updateNotificationCount, 5000);
        window.setInterval(refreshLiveRegions, 5000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
