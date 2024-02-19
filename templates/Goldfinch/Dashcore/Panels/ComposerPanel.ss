<span><strong>Packages installed:</strong> $list.count</span>
<br /><br />
<table class="table">
  <thead>
    <tr>
      <td>Name</td>
      <td>Version</td>
    </tr>
  </thead>
  <% loop list %>
  <tr>
    <td>
      <a target="_blank" href="https://github.com/{$name}">$name</a>
    </td>
    <td>$version</td>
  </tr>
  <% end_loop %>
</table>
