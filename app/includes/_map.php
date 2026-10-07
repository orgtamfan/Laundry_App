<?php
	$map = outletMap();
	$lat = $map['lat'];
	$lng = $map['lng'];
	$bbox = ($lng - 0.01) . ',' . ($lat - 0.01) . ',' . ($lng + 0.01) . ',' . ($lat + 0.01);
	$marker = $lat . ',' . $lng;
?>

<div class="baris">
	<div class="col">
		<div class="card map-card">
			<div class="card-title card-flex">
				<div class="card-col">
					<h2>Lokasi Outlet</h2>
					<p><?= $map['name'] ?> - <?= $map['address'] ?></p>
				</div>
				<div class="card-col txt-right">
					<a
						href="https://www.openstreetmap.org/?mlat=<?= $lat ?>&mlon=<?= $lng ?>#map=16/<?= $lat ?>/<?= $lng ?>"
						target="_blank"
						rel="noopener"
						class="btn-xs bg-primary"
					>Lihat Peta</a>
				</div>
			</div>

			<div class="card-body">
				<div class="map-frame">
					<iframe
						title="Peta lokasi <?= $map['name'] ?>"
						src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $bbox ?>&layer=mapnik&marker=<?= $marker ?>"
						loading="lazy"
					></iframe>
				</div>
			</div>
		</div>
	</div>
</div>
