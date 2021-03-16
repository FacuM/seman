$(document).ready(() => {
    const
        DEFAULT_TOAST_FADEIN_DELAY  = 250,
        DEFAULT_TOAST_FADEOUT_DELAY = 5000;

    let progressBar     = $('#serverListProgress').find('.progress-bar');
    let serverListTable = $('#serverListTable').find('tbody');
    let serverImage     = $('#serverImage');

    let serverDataModal         = $('#serverDataModal');
    let serverDescription       = $('#serverDescription');
    let serverHostname          = $('#serverHostname');
    let serverIP                = $('#serverIP');
    let serverImageLabel        = $('#serverImage').parent().find('label');
    let serverImageDefaultLabel = serverImageLabel.text();
    let serverImageData         = null;

    let removeServerModal         = $('#removeServerModal');
    let removeConfirmBtn          = $('#removeConfirmBtn');
    let removeConfirmDefaultLabel = $('#removeConfirmBtn').text();

    let serverListCount = $('#serverListCount');

    let serverStatusModal = $('#serverStatusModal');

    let dragging = null, currentOrder = null;

    let createChart = (vanillaElement, dataset) => {
        let labels = []; let data = [];

        Chart.helpers.each(Chart.instances, function(instance){
            if (instance.chart.canvas.id == vanillaElement.id) {
                instance.destroy();
            }
        });

        dataset.forEach((serverStatus) => {
            labels.push(serverStatus.label);
            data.push(serverStatus.data);
        });

        labels.unshift('No data');
        data.unshift(0);

        return new Chart(vanillaElement, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    lineTension: 0,
                    backgroundColor: 'transparent',
                    borderColor: '#007bff',
                    borderWidth: 4,
                    pointBackgroundColor: '#007bff'
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });
    };

    let attachServerListEvents = () => {
        let tableRows = serverListTable.find('tr');

        // Track which element is being dragged
        tableRows.on('dragstart', (event) => {
            let target = $(event.target);

            while (typeof(target.attr('draggable')) == 'undefined') {
                target = target.parent();
            }

            console.log('over', $(target));

            dragging = target;

            currentOrder = [];

            tableRows = serverListTable.find('tr');

            tableRows.each(function () {
                rowId = $(this).data().id;

                currentOrder.push(rowId);
            });
        });

        // Allow "drop" to trigger
        tableRows.on('dragover', (event) => {
            // Prevent default, allow drop.
            event.preventDefault();
        });

        // Handle drop by swapping A and B
        tableRows.on('drop', (event) => {
            // Workaround default "bubbling" behavior: "drop" runs for N prevented "dragover(s)".
            event.stopImmediatePropagation();

            let target = $(event.target);

            while (typeof(target.attr('draggable')) == 'undefined') {
                target = target.parent();
            }

            if (target != dragging && dragging != null) {
                self = target.clone();

                target.replaceWith(dragging.clone());
                dragging.replaceWith(self);

                console.log('drop', target, dragging);

                newOrder = [];

                tableRows = serverListTable.find('tr');

                tableRows.each(function () {
                    rowId = $(this).data().id;

                    newOrder.push(rowId);
                });

                let isOrderDifferent = false;

                newOrder.forEach((value, key) => {
                    if (currentOrder[key] != newOrder[key]) {
                        isOrderDifferent = true;
                    }
                });

                if (isOrderDifferent) {
                    createToast('savingOrderModal', 'Saving', 'The changes you made are being saved.', false, false);

                    $.ajax({
                        type: 'POST',
                        url: 'services/serverListManager.php',
                        data: JSON.stringify({
                            operation: 'saveOrder',
                            values: { order: newOrder }
                        })
                    })
                    .done((response) => {
                        console.log(response);
                
                        switch (response.status) {
                            case HTTP_STATUS.DATABASE_ERROR:
                                showDatabaseErrorToast();

                                break;
                        }
                    })
                    .fail((error) => {
                        console.error(error);

                        showGenericErrorToast();
                    })
                    .always(() => {
                        removeToast('savingOrderModal');
                    });
                }

                attachServerListEvents();
            }
        });

        serverListTable.find('[data-image]').on('click', (event) => {
            serverImageModal = $('#serverImageModal');

            modalBody = serverImageModal.find('.modal-body');
            
            modalBody.html('');

            let target = $(event.target);

            while (typeof(target.data().image) == 'undefined') {
                target = target.parent();
            }

            img         = document.createElement('img');
            img.src     = IMAGE_UPLOAD_PATH + `/` + target.data().image;
            img.onerror = (event) => {
                target = $(event.target);

                target.parent().append('<p class="mt-2"> Couldn\'t load image. </p>');
                target.remove();
            }

            modalBody.append(img);

            serverImageModal.modal('show');
        });

        tableRows.find('[data-edit]').on('click', (event) => {
            let target = $(event.target);

            while (typeof(target.data().edit) == 'undefined') {
                target = target.parent();
            }

            serverLoadingToast = createToast('serverLoadingToast', 'Processing data', 'We\'re retrieving the contents of the server you selected, please wait for a while.', false, false);

            let toEditId = target.data().edit;

            serverDataModal.data('edit', toEditId);

            $.ajax({
                type: 'POST',
                url: 'services/serverListManager.php',
                data: JSON.stringify({
                    operation: 'getServer',
                    values: { id: toEditId }
                })
            })
            .done((response) => {
                console.log(response);
        
                switch (response.status) {
                    case HTTP_STATUS.OK:
                        if (response.result == null) {
                            createToast('noSuchServer', 'No such server', 'We were unable to find the server you tried to edit, please reload the page and try again.');
                        } else {
                            const server = response.result;

                            serverDataModal.data('edir', server.id);

                            serverDataModal.find('.modalMode').html('Edit');

                            serverDescription.val(server.description);
                            serverHostname.val(server.hostname);
                            serverIP.val(server.ip);

                            serverDataModal.find('textarea, input').trigger('change');

                            serverImageLabel.html(server.image);

                            serverDataModal.modal('show');
                        }

                        break;
                    case HTTP_STATUS.DATABASE_ERROR:
                        createToast('crashToast', 'Something went wrong', 'An unexpected exception caused your request to fail, please try again.');

                        break;
                }
            })
            .fail((error) => {
                console.error(error);

                showGenericErrorToast();
            })
            .always(() => {
                removeToast('serverLoadingToast');
            });
        });

        tableRows.find('[data-remove]').on('click', (event) => {
            let target = $(event.target);

            while (typeof(target.data().remove  ) == 'undefined') {
                target = target.parent();
            }

            let toRemoveId = target.data().remove;

            removeServerModal.data('remove', toRemoveId);
            removeServerModal.data('confirmed', false);

            $('#removeServerModal').modal('show');
        });

        tableRows.find('[data-query]').on('click', (event) => {
            let target = $(event.target);

            while (typeof(target.data().query) == 'undefined') {
                target = target.parent();
            }

            createToast('serverLoadingToast', 'Processing data', 'We\'re retrieving the status of the server you selected, please wait for a while.', false, false);

            let toQueryId = target.data().query;

            $.ajax({
                type: 'POST',
                url: 'services/serverListManager.php',
                data: JSON.stringify({
                    operation: 'getServerStatus',
                    values: { id: toQueryId }
                })
            })
            .done((response) => {
                console.log(response);
        
                switch (response.status) {
                    case HTTP_STATUS.OK:
                        if (response.result == null) {
                            createToast('noSuchServer', 'No such server', 'We were unable to find the server you tried to query, please reload the page and try again.');
                        } else {
                            createChart($('#processesChart')[0], response.result.processes);
                            createChart($('#sessionsChart')[0], response.result.sessions);

                            serverStatusModal.modal('show');

                            if (response.result.hadIssues) {
                                createToast('queryHadIssues', 'We couldn\'t reach the server', 'The server didn\'t reply on time, so you\'re seeing historic data without live information.', false, false);
                            }
                        }

                        break;
                    case HTTP_STATUS.DATABASE_ERROR:
                        createToast('crashToast', 'Something went wrong', 'An unexpected exception caused your request to fail, please try again.');

                        break;
                }
            })
            .fail((error) => {
                console.error(error);

                showGenericErrorToast();
            })
            .always(() => {
                removeToast('serverLoadingToast');
            });
        });

        feather.replace();

        $('[data-toggle="tooltip"]').tooltip();
    };

    let createToast = (type, title, content, allowDismiss = true, autoHide = true, delay = DEFAULT_TOAST_FADEOUT_DELAY) => {
        $('#toastsContainer').append(
            `<div class="toast ` + type + `" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="` + autoHide.toString() + `" data-delay="` + delay + `">
                <div class="toast-header">
                    <strong class="mr-auto"> ` + title + ` </strong>
                    <small> just now </small>` + (allowDismiss ? `
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>` : ``) + `
                </div>
                <div class="toast-body"> ` + content + ` </div>
             </div>`
        );

        toast = $('.toast.' + type).toast('show');

        toast.on('hidden.bs.toast', () => { toast.remove(); });

        return toast;
    };

    let removeToast = (toastClass) => {
        setTimeout(() => {
            // Using replaceAll to prevent duplicated 
            toast = $('.' + toastClass);

            if (toast.length < 1) {
                console.info('removeToast: tried to remove a non-existing toast, skipping...');
            } else {
                toast.toast('hide');

                toast.on('hidden.bs.toast', () => { toast.remove(); });
            }
        }, DEFAULT_TOAST_FADEIN_DELAY);
    };

    let showGenericErrorToast = () => (
        createToast('genericCrashToast', 'Something went wrong', 'An unexpected error has occured and it couldn\'t be handled, please try again later.')
    );

    let showDatabaseErrorToast = () => (
        createToast('crashToast', 'Something went wrong', 'An unexpected exception caused your changes to get lost, please try again.')
    );

    let getServerListRow = (server) => (
        `<tr draggable="true" data-id="` + server.id + `">
            <td>
                <button
                    type="button"
                    class="btn btn-success btn-sm"
                    data-image="` + server.image + `"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="View server image"
                >
                    <span data-feather="eye"></span>
                </button>
            </td>
            <td> ` + server.description + ` </td>
            <td> ` + server.hostname    + ` </td>
            <td> ` + server.ip          + ` </td>
            <td>
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    data-query="` + server.id + `"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Query server status"
                >
                    <span data-feather="activity"></span>
                </button>
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    data-edit="` + server.id + `"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Edit server"
                >
                    <span data-feather="edit"></span>
                </button>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    data-remove="` + server.id + `"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Remove server"
                >
                    <span data-feather="trash"></span>
                </button>
            </td>
         </tr>`
    );

    $.ajax({
        xhr: () => {
            let xhr = new window.XMLHttpRequest();

            xhr.addEventListener('progress', (event) => {
                let loadedPercentage = Math.round((event.loaded * 100) / event.total);

                if (loadedPercentage > 100) {
                    loadedPercentage = 100;
                }

                progressBar
                    .attr('aria-valuenow', loadedPercentage      )
                    .css('width'         , loadedPercentage + '%');
            });

            return xhr;
        },
        type: 'POST',
        url: 'services/serverListManager.php',
        data: JSON.stringify({ 'operation': 'getServers' })
    })
    .done((response) => {
        console.log(response);

        switch (response.status) {
            case HTTP_STATUS.OK:
                if (response.result.length > 0) {
                    serverListBody = '';

                    response.result.forEach((server) => {
                        serverListBody += getServerListRow(server);
                    });

                    serverListTable.html(serverListBody);

                    serverListCount.html(response.result.length);

                    attachServerListEvents();
                }

                serverListTable.append(
                    `<tr id="noServersDummy" style="display: ` + (response.result.length > 0 ? `none` : ``) + `;">
                        <td colspan="5">
                            This list is empty, feel free to add a server!
                        </td>
                     </tr>`
                );

                break;
            case HTTP_STATUS.DATABASE_ERROR:
                break;
        }
    })
    .fail((error) => {
        console.error(error);

        showGenericErrorToast();
    })
    .always(() => {
        progressBar.parent().fadeOut(() => {
            $('#serverListTable').fadeIn();
        });
    });

    $('#addServerBtn').on('click', () => {
        serverDataModal.find('modalMode').html('Add');

        serverDataModal.modal('show');
    });

    serverDescription.on('change keyup keydown', (event) => {
        let target = $(event.target);

        if (target.val().length > 0) {
            target.removeClass('is-invalid').addClass('is-valid');
        } else {
            target.removeClass('is-valid is-invalid');
        }
    });

    serverHostname.on('change keyup keydown', (event) => {
        let target = $(event.target);
        let value  = target.val();

        if (value.length > 0 && value.length < 256) {
            if (validator.isFQDN(value)) {
                target.removeClass('is-invalid').addClass('is-valid');
            } else {
                target.addClass('is-invalid').removeClass('is-valid');
            }
        } else {
            target.removeClass('is-valid is-invalid');
        }
    });

    serverIP.on('change keyup keydown', (event) => {
        let target = $(event.target);
        let value  = target.val();

        if (value.length > 0 && value.length < 256) {
            if (validator.isIP(value)) {
                target.removeClass('is-invalid').addClass('is-valid');
            } else {
                target.addClass('is-invalid').removeClass('is-valid');
            }
        } else {
            target.removeClass('is-valid is-invalid');
        }
    });

    serverImage.on('change', () => {
        let files = serverImage[0].files;
        
        if (files.length > 0) {
            let file = files[0];

            // Safari workaround
            if (typeof(file.name) == 'undefined') {
                file.name = 'Image';
            }

            if (
                file.type.indexOf('image/jpg') > -1
                ||
                file.type.indexOf('image/jpeg') > -1
                ||
                file.type.indexOf('image/png') > -1
                ||
                file.type.indexOf('image/gif') > -1
            ) {
                serverImage.addClass('is-valid').removeClass('is-invalid');

                serverImageLabel.text(file.name);

                const reader = new FileReader();

                reader.addEventListener('load', (event) => {
                    let result = event.target.result;

                    serverImageData = result.split(',')[1];
                });

                reader.readAsDataURL(file);
            } else {
                serverImage.removeClass('is-valid is-invalid');

                serverImageLabel.text(serverImageDefaultLabel);

                serverImageData = null;

                createToast('invalidImageToast', 'Invalid image', 'The file you selected doesn\'t appear to be a valid image. Please make sure that it\'s either a jpg, jpeg, gif or png file, its size must be 300x300 px.');
            }
        } else {
            serverImage.removeClass('is-valid is-invalid');

            serverImageLabel.text(serverImageDefaultLabel);

            serverImageData = null;
        }
    });

    $('#saveServerBtn').on('click', () => {
        let willSubmit = true;
        let toEditId = serverDataModal.data().edit;

        serverDataModal.find('textarea, input').each(function () {
            if ($(this).attr('id') != 'serverImage' || toEditId == null) {
                if (!$(this).hasClass('is-valid')) {
                    $(this).addClass('is-invalid');

                    willSubmit = false;
                }
            }
        });

        if (willSubmit) {
            let operationPrefix = toEditId == null ? 'add' : 'edit';

            let values = {
                edit:           toEditId,
                description:    serverDescription.val(),
                hostname:       serverHostname.val(),
                ipAddress:      serverIP.val(),
                image:          serverImageData
            };

            console.log('serverManager/' + operationPrefix + ':', values);

            $.ajax({
                type: 'POST',
                url: 'services/serverListManager.php',
                data: JSON.stringify({
                    operation: operationPrefix + 'Server',
                    values: values
                })
            })
            .done((response) => {
                console.log(response);

                switch (response.status) {
                    case HTTP_STATUS.DATABASE_ERROR:
                        showDatabaseErrorToast();

                        break;
                    case HTTP_STATUS.OK:
                        newRow = getServerListRow({
                            id:             response.result.id,
                            description:    serverDescription.val(),
                            hostname:       serverHostname.val(),
                            ip:             serverIP.val(),
                            image:          response.result.image
                        });

                        if (toEditId == null) {
                            onDummyHidden = () => {
                                serverListTable.append(newRow);

                                currentCount = parseInt(serverListCount.text());

                                serverListCount.text(currentCount + 1);

                                attachServerListEvents();
                            };

                            if ($('#noServersDummy').is(':visible')) {
                                $('#noServersDummy').fadeOut(onDummyHidden);
                            } else {
                                onDummyHidden();
                            }
                        } else {
                            $('tr[data-id="' + toEditId + '"]').replaceWith(newRow);
                        }

                        attachServerListEvents();

                        serverDataModal.modal('hide');

                        break;
                    case HTTP_STATUS.BAD_REQUEST:
                        Object.keys(response.errors).forEach((error) => {
                            switch (error) {
                                case 'edit':
                                    createToast('invalidEdit', 'Invalid server', 'The server you tried to edit, isn\'t valid.');

                                    break;
                                case 'description':
                                    serverDescription.addClass('is-invalid');

                                    break;
                                case 'hostnameValidator':
                                    serverHostname.addClass('is-invalid');

                                    break;
                                case 'ipAddress':
                                    serverIP.addClass('is-invalid');

                                    break;
                                case 'image':
                                    serverImage.addClass('is-invalid');

                                    break;
                            }
                        });

                        break;
                }
            })
            .fail((error) => {
                console.error(error);

                showGenericErrorToast();
            });
        }
    });

    serverDataModal.on('hidden.bs.modal', () => {
        serverDataModal
            .data('edit', null)
            .find('textarea, input')
            .val('')
            .trigger('change');
    });

    removeServerModal.on('hidden.bs.modal', () => {
        removeServerModal
            .data('remove', null)

        removeConfirmBtn.text(removeConfirmDefaultLabel);
    });

    $('#removeConfirmBtn').on('click', () => {
        if (!removeServerModal.data().confirmed) {
            removeServerModal.data('confirmed', true);

            removeConfirmBtn.text('Again!');

            return;
        }

        let toRemoveId = removeServerModal.data().remove;

        createToast('removingServerToast', 'Removing server', 'We\'re removing the server you selected, please wait for a while.', false, false);

        $.ajax({
            type: 'POST',
            url: 'services/serverListManager.php',
            data: JSON.stringify({
                operation: 'removeServer',
                values: { id: toRemoveId }
            })
        })
        .done((response) => {
            console.log(response);
    
            switch (response.status) {
                case HTTP_STATUS.OK:
                    toRemoveElement = $('tr[data-id="' + toRemoveId + '"]');

                    removeServerModal.modal('hide');

                    currentCount = parseInt(serverListCount.text());
                    targetCount = currentCount - 1;

                    serverListCount.text(targetCount);

                    toRemoveElement.fadeOut(() => {
                        toRemoveElement.remove();

                        if (targetCount < 1) {
                            $('#noServersDummy').fadeIn();
                        }
                    });

                    break;
                case HTTP_STATUS.DATABASE_ERROR:
                    showDatabaseErrorToast();

                    break;
            }
        })
        .fail((error) => {
            console.error(error);

            showGenericErrorToast();
        })
        .always(() => {
            removeToast('removingServerToast');
        });
    });

    serverStatusModal.on('hide.bs.modal', () => {
        removeToast('queryHadIssues');
    });
});