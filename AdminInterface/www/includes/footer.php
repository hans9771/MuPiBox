
			<div id="footer">
				<div id="footerleft">
					<ul>
						<li><a href="https://mupibox.de/" target="_blank">MuPiBox.de</a></li>
						<li><a href="https://discord.gg/4EjCgpCbbe" target="_blank">Support on Discord</a></li>
						<li><a href="https://www.youtube.com/@mupibox" target="_blank">MuPiBox on Youtube</a></li>
					</ul>
				</div>
				<div id="footercenter">
					<ul>
						<li><a href="https://github.com/hans9771/MuPiBox" target="_blank">Visit MupiBox on Github</a></li>
						<li><a href="https://github.com/hans9771/MuPiBox/blob/main/LICENSE.md" target="_blank">License / Copyright</a></li>						
					</ul>
				</div>
				<div id="footerright">
					<img id="mupi" src="images/mupi.png" alt="I am Mupi and i like audiobooks..." title="I am Mupi and i like audiobooks...">
				</div>

			</div>
			<div id="lock-modal"></div>
			<div id="loading-circle"></div>
			<div id="mupif"><img src="images/mupif.png" /></div>
		</div>
		<img id="bottom" src="images/bottom.png" alt="">
<?php
	if( $change )
		{
		print '<div class="lightbox"><div class="iframeContainer"><div class="toolbarLB"><div class="closeLB" onclick="lightBoxClose()"><div class="closeLBSym">+</div></div></div><p>'.$CHANGE_TXT.'DONE</p></div></div>';
		} 
?>
	</body>
</html>

<?php
	if( $reboot == 1 )
		{
		$command='sudo su - -c "sleep 5; /usr/local/bin/mupibox/./restart.sh &" &';
		exec($command);
		}
	if( $shutdown == 1 )
		{
		$command='sudo su - -c "sleep 5; /usr/local/bin/mupibox/./shutdown.sh &" &';
		exec($command);
		}
?>
