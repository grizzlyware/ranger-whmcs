<style>

    .ranger .panel
    {
        font-size: 1em;
    }

    .ranger .panel .alert
    {
        margin-bottom: 0px;
    }

</style>

<div class="text-left ranger">

    <div class="row">

        <div class="col-sm-6">

            <div class="panel {if $ranger.license.status->status == 'inactive'}panel-danger{elseif $ranger.license.status->status == 'reissued'}panel-warning{elseif $ranger.license.status->status == 'locked'}panel-success{elseif $ranger.license.status->status == 'pending'}panel-info{/if}">

                <div class="panel-heading">

                    <h3 class="panel-title">License status</h3>

                </div>

                <div class="panel-body">

                    {if $ranger.license.status->status == 'inactive'}

                        <i class="fas fa-times-circle"></i> Inactive

                    {elseif $ranger.license.status->status == 'reissued'}

                        <i class="fas fa-redo"></i> Reissued

                    {elseif $ranger.license.status->status == 'locked'}

                        <i class="fas fa-lock"></i> Active

                    {elseif $ranger.license.status->status == 'pending'}

                        <i class="fas fa-tasks"></i> Pending generation

                    {/if}

                </div>

            </div>

            <div class="panel panel-default">

                <div class="panel-heading">

                    <h3 class="panel-title">Your license key</h3>

                </div>

                <div class="panel-body">

                    {if $ranger.license.key}

                        <code>{$ranger.license.key}</code>

                    {else}

                        <p>Your license hasn't been generated yet</p>

                    {/if}

                </div>

            </div>

        </div>

        <div class="col-sm-6">

            <div class="panel panel-warning">

                <div class="panel-heading">

                    <h3 class="panel-title">Reissue</h3>

                </div>

                <div class="panel-body">

                    <p>Reissuing your license will allow you to change where it's installed. The installation environment will be locked next time the license is validated.</p>

                    {if $ranger.license.clientCanReissue}

                        {if $ranger.license.status->status == 'locked'}

                            <a href="clientarea.php?action=productdetails&id={$id}&modop=custom&a=reissue" class="btn btn-primary"><i class="fas fa-refresh"></i> Reissue</a>

                        {elseif $ranger.license.status->status == 'reissued'}

                            {if $modulecustombuttonresult && $smarty.get.a == 'reissue'}

                                {if $modulecustombuttonresult == 'success'}

                                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Reissued successfully</div>

                                {else}

                                    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error reissuing license</div>

                                {/if}

                            {else}

                                <div class="alert alert-info"><i class="fas fa-check-circle"></i> Already reissued</div>

                            {/if}

                        {elseif $ranger.license.status->status == 'inactive'}

                            <div class="alert alert-info"><i class="fas fa-times-circle"></i> Reissuing unavailable</div>

                        {elseif $ranger.license.status->status == 'pending'}

                            <div class="alert alert-info"><i class="fas fa-info-circle"></i> Not yet generated</div>

                        {/if}

                    {else}

                        <div class="alert alert-danger">You must contact us to reissue this license</div>

                    {/if}

                </div>

            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-sm-12">

            <h3>Installation environment</h3>

            <p>When you use the license key, it'll be validated against your previous installation environments. The information below shows where your license is currently available to use. If you wish to change environments, you may reissue your license.</p>

        </div>

    </div>

    <div class="row">

        <div class="col-md-6">

            <div class="panel panel-default">

                <div class="panel-heading">

                    <h3 class="panel-title">Hostnames</h3>

                </div>

                <ul class="list-group">

                    {foreach from=$ranger.license.environment.hostnames item="envProperty"}

                        <li class="list-group-item"><code>{$envProperty|htmlentities}</code></li>

                    {foreachelse}

                        <li class="list-group-item">No hostnames set</li>

                    {/foreach}

                </ul>

            </div>

        </div>

        <div class="col-md-6">

            <div class="panel panel-default">

                <div class="panel-heading">

                    <h3 class="panel-title">IP Addresses</h3>

                </div>

                <ul class="list-group">

                    {foreach from=$ranger.license.environment.ips item="envProperty"}

                        <li class="list-group-item"><code>{$envProperty|htmlentities}</code></li>

                    {foreachelse}

                        <li class="list-group-item">No IP addresses set</li>

                    {/foreach}

                </ul>

            </div>

        </div>

        <div class="col-md-12">

            <div class="panel panel-default">

                <div class="panel-heading">

                    <h3 class="panel-title">Directories</h3>

                </div>

                <ul class="list-group">

                    {foreach from=$ranger.license.environment.directories item="envProperty"}

                        <li class="list-group-item"><code>{$envProperty|htmlentities}</code></li>

                    {foreachelse}

                        <li class="list-group-item">No directories set</li>

                    {/foreach}

                </ul>

            </div>

        </div>

    </div>

</div>