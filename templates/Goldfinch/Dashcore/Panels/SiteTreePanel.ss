<ul class="dashcard__list">
  <% loop list %>
  <li>
    <a href="$link" title="$title" target="_self">
      <i class="$icon"></i>
      <span class="item__name">$title</span>
    </a>
    <span class="item__author" title="$updated_at"
      ><% if author %><span>$author, </span
      ><% end_if %>$updated_at_human</span
    >
  </li>
  <% end_loop %>
</ul>
