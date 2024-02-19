<span><strong>Current branch:</strong> $mainbranch</span>
<br /><br />
<h3>All branches</h3>
<ul>
  <% loop branches %>
  <li>
    <span style="<% if $main %>font-weight: 600<% end_if %>">$name</span>
  </li>
  <% end_loop %>
</ul>
<br />
<h3>Last commits</h3>
<table class="table">
  <thead>
    <tr>
      <td>Author</td>
      <td>Commit</td>
      <td>Message</td>
      <td>Date</td>
      <td>Builds</td>
    </tr>
  </thead>
  <% loop commits %>
  <tr>
    <td>$author</td>
    <td>
      <a href="#{$hash}">$hashShort</a>
    </td>
    <td>$commit</td>
    <td title="$dateFull">$dateNow</td>
    <td>-</td>
  </tr>
  <% end_loop %>
</table>
