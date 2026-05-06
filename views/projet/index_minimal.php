<h2>Projets</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nom</th>
    </tr>
    <?php foreach ($projets as $row): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['nomprojet']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
