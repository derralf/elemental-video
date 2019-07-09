    <div class="row">
        <div class="col-xs-12 col-sm-6 col-sm-push-6">
            <% if $ShowTitle %>
                <% include Derralf\\Elements\\TextImages\\Title %>
            <% end_if %>

            <% if $HTML %>
                <div class="element__content">$HTML</div>
            <% end_if %>

            <% if $ReadMoreLink.LinkURL %>
                <div class="element__readmorelink"><p><a href="$ReadMoreLink.LinkURL" class="{$ReadmoreLinkClass}" {$ReadMoreLink.TargetAttr} ><% if $ReadMoreLink.Title %>$ReadMoreLink.Title<% else %> mehr<% end_if %></a></p></div>
            <% end_if %>
        </div>
        <div class="col-xs-12 col-sm-6 col-sm-pull-6 image-wrap image-wrap-left">
            <% if $EmbedCode %>
                $EmbedCode.RAW
                <% if $MediaCredits %><div class="small">$MediaCredits</div><% end_if %>
            <% else %>
                <div class="alert alert-warning" role="alert">Video nicht gefunden</div>
            <% end_if %>
        </div>
    </div>











