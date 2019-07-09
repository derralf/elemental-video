    <% if $ShowTitle %>
        <% include Derralf\\Elements\\TextImages\\Title %>
    <% end_if %>

    <% if $HTML %>
        <div class="element__content">$HTML</div>
    <% end_if %>

    <% if $ReadMoreLink.LinkURL %>
        <div class="element__readmorelink"><p><a href="$ReadMoreLink.LinkURL" class="{$ReadmoreLinkClass}" {$ReadMoreLink.TargetAttr} ><% if $ReadMoreLink.Title %>$ReadMoreLink.Title<% else %> mehr<% end_if %></a></p></div>
    <% end_if %>

    <% if $EmbedCode %>
        $EmbedCode.RAW
        <% if $MediaCredits %><div class="small">$MediaCredits</div><% end_if %>
    <% else %>
        <div class="alert alert-warning" role="alert">Video nicht gefunden</div>
    <% end_if %>






