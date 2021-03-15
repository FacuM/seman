<?php

$initializeDatabase = false; $section = 'Dashboard';

$css = [ 'assets/css/dashboard.css' ];

require_once 'views/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"> <?= $section ?> </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <button id="addServerBtn" class="btn btn-sm btn-outline-secondary"> Add server </button>
        </div>
    </div>
</div>

<div id="serverListProgress" class="progress">
    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
</div>

<div id="serverListTable" class="table-responsive" style="display: none;">
    <table class="table table-striped table-sm text-center border-bottom">
        <thead>
            <tr>
                <th> Image       </th>
                <th> Description </th>
                <th> Hostname    </th>
                <th> IP address  </th>
                <th> Actions     </th>
            </tr>
        </thead>
        <tbody>
            <!-- To be filled through Javascript -->
        </tbody>
    </table>

    <p class="text-right">
        Displaying a total of <span id="serverListCount">0</span> servers.
    </p>
</div>

<div id="serverImageModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="myExtraLargeModalLabel">
                    Server image
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center"></div>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="serverDataModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="serverDataModalLabel"
    aria-hidden="true"
    data-edit="null"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serverDataModalLabel"> <span class="modalMode"> Add </span> server </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="serverDescription" class="col-form-label"> Description </label>
                        <textarea class="form-control" id="serverDescription"></textarea>
                        <div class="invalid-feedback">
                            Please provide a valid description.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="serverHostname" class="col-form-label">Hostname</label>
                        <input type="text" class="form-control" id="serverHostname">
                        <div class="invalid-feedback">
                            Please provide a valid hostname.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="serverIP" class="col-form-label">IP address</label>
                        <input type="text" class="form-control" id="serverIP">
                        <div class="invalid-feedback">
                            Please provide a valid IP address.
                        </div>
                    </div>
                    <label class="col-form-label"> Image </label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="serverImage">
                        <label id="serverImageLabel" class="custom-file-label" for="serverImage">Choose file...</label>
                        <div class="invalid-feedback">
                            Invalid image, please select either a jpg/jpeg, gif or png, all 300x300 px.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button id="saveServerBtn" type="button" class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<div id="removeServerModal" class="modal fade" tabindex="-1" role="dialog" data-remove="null" data-confirmed="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Remove server </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Do you really want to remove this server?
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"> No </button>
                <button id="removeConfirmBtn" type="button" class="btn btn-danger"> Yes </button>
            </div>
        </div>
    </div>
</div>

<div id="serverStatusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="myExtraLargeModalLabel">
                    Server status
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-center"> # of active processes </p>
                <canvas class="my-4 border-bottom" id="processesChart" width="900" height="380"></canvas>
                <p class="text-center"> # of active sessions </p>
                <canvas class="my-4 border-bottom" id="sessionsChart" width="900" height="380"></canvas>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>