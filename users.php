<?php
require_once "sessions.php";
require "sql.php";
require "_header.php";

$query = sprintf("SELECT username, email, status, reg_date FROM %s", $config["sql_t_users"]);
$users = $conn->query($query);
?>

<h2>Users on this forum</h2>

<table style="width: 100%;">
  <tbody>
    <tr>
      <th>Username</th>
      <th>E-mail</th>
      <th>Status</th>
      <th>Registered</th>
    </tr>
    <?php
      while ($row = $users->fetch_assoc())
      {
        echo "<tr>";

        echo "<td>" . $row["username"] . "</td>";

        echo "<td>" . $row["email"] . "</td>";

        echo "<td>" . $row["status"] . "</td>";

        echo "<td>" . $row["reg_date"] . "</td>";

        echo "</tr>";
      }
    ?>
  </tbody>
</table>

<?php require "_footer.php"; ?>
