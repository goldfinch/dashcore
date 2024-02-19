<% if list %>
<ul class="dashcard__list">
  <% loop list %>
  <li>
    <a href="$link" title="$full_title" target="_self">
      <img src="$icon" width="160" />
      <span class="item__name" title="$full_title">$title</span>
      <span>$specs</span>
    </a>
    <span class="item__author" title="$updated_at"
      ><% if author %><span>$author, </span
      ><% end_if %>$updated_at_human</span
    >
  </li>
  <% end_loop %>
</ul>
<% else %>
<span class="text-muted">Blocks not found</span>
<% end_if %>
