/* global acss3to4 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const btn    = document.getElementById( 'acss3to4-start' );
		const logBox = document.getElementById( 'acss3to4-log' );
		const list   = document.getElementById( 'acss3to4-log-list' );
		const done   = document.getElementById( 'acss3to4-done' );
		const regen  = document.getElementById( 'acss3to4-regen' );

		if ( ! btn ) {
			return;
		}

		regen.href = acss3to4.acssUrl;

		btn.addEventListener( 'click', async function () {
			btn.disabled    = true;
			logBox.hidden   = false;

			try {
				await runStep1();
				await runStep2();
				await runStep3();
				done.hidden = false;
				addLog( '✓ Migration complete.', 'success' );
			} catch ( err ) {
				addLog( '✗ ' + err.message, 'error' );
				btn.disabled = false;
			}
		} );

		async function runStep1() {
			setStatus( 'step1', 'running' );
			const res = await ajax( 'acss3to4_step1', {} );
			if ( res.success === false ) {
				setStatus( 'step1', 'error' );
				throw new Error( 'Step 1 failed: ' + ( res.message || '' ) );
			}
			setStatus( 'step1', 'done' );
			addLog( '✓ ' + res.message, 'success' );
		}

		async function runStep2() {
			setStatus( 'step2', 'running' );

			let offset         = 0;
			let total          = 1;
			let totalConverted = 0;
			let totalFlagged   = 0;
			const flaggedIds   = [];

			while ( offset < total ) {
				const res  = await ajax( 'acss3to4_step2', { offset } );
				offset     = res.processed;
				total      = res.total || 1;
				totalConverted += res.converted || 0;
				totalFlagged   += res.flagged   || 0;

				if ( res.flagged_ids && res.flagged_ids.length ) {
					flaggedIds.push( ...res.flagged_ids );
				}

				setProgress( 'step2', offset, total );
			}

			setStatus( 'step2', 'done' );

			let msg = '✓ ' + total + ' posts scanned — ' + totalConverted + ' conversions';
			if ( totalFlagged > 0 ) {
				const ids = [ ...new Set( flaggedIds ) ].join( ', #' );
				msg      += ', ' + totalFlagged + ' flagged for manual review → posts: #' + ids;
			}
			addLog( msg, totalFlagged > 0 ? 'warning' : 'success' );
		}

		async function runStep3() {
			setStatus( 'step3', 'running' );
			const res = await ajax( 'acss3to4_step3', {} );
			setStatus( 'step3', 'done' );
			addLog( '✓ bricks_global_classes: ' + ( res.updated_count || 0 ) + ' class names updated', 'success' );
		}

		function ajax( action, data ) {
			return new Promise( function ( resolve, reject ) {
				const body = new URLSearchParams( { action, nonce: acss3to4.nonce, ...data } );
				fetch( acss3to4.ajaxUrl, { method: 'POST', body } )
					.then( function ( r ) { return r.json(); } )
					.then( resolve )
					.catch( reject );
			} );
		}

		function setStatus( step, status ) {
			const el = document.querySelector( '[data-step="' + step + '"] .step-status' );
			if ( ! el ) { return; }
			const icons = { running: '⟳', done: '✓', error: '✗' };
			el.textContent = icons[ status ] || '○';
			el.className   = 'step-status step-status--' + status;
		}

		function setProgress( step, processed, total ) {
			const el = document.querySelector( '[data-step="' + step + '"] .step-progress' );
			if ( ! el ) { return; }
			el.textContent = ' (' + processed + ' / ' + total + ')';
		}

		function addLog( message, type ) {
			const li       = document.createElement( 'li' );
			li.textContent = message;
			li.className   = 'log-' + type;
			list.appendChild( li );
		}
	} );
}() );
