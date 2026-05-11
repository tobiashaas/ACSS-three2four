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
			let total          = 0;
			let totalConverted = 0;
			let totalFlagged   = 0;
			const flaggedIds   = [];
			const details      = [];

			while ( true ) {
				const res  = await ajax( 'acss3to4_step2', { offset } );
				if ( res.success === false ) {
					setStatus( 'step2', 'error' );
					throw new Error( 'Step 2 failed: ' + ( res.data || res.message || '' ) );
				}

				const nextOffset = Number( res.processed || 0 );
				const nextTotal  = Number( res.total || 0 );

				if ( nextTotal <= 0 ) {
					total  = 0;
					offset = 0;
					setProgress( 'step2', 0, 0 );
					break;
				}

				if ( nextOffset <= offset && nextTotal > offset ) {
					setStatus( 'step2', 'error' );
					throw new Error( 'Step 2 stalled before completing all posts.' );
				}

				offset     = nextOffset;
				total      = nextTotal;
				totalConverted += res.converted || 0;
				totalFlagged   += res.flagged   || 0;
				if ( Array.isArray( res.details ) && res.details.length ) {
					details.push( ...res.details );
				}

				if ( res.flagged_ids && res.flagged_ids.length ) {
					flaggedIds.push( ...res.flagged_ids );
				}

				setProgress( 'step2', offset, total );

				if ( offset >= total ) {
					break;
				}
			}

			setStatus( 'step2', 'done' );

			let msg = '✓ ' + total + ' posts scanned — ' + totalConverted + ' conversions';
			if ( total === 0 ) {
				msg = '✓ No Bricks posts found for Step 2.';
			}
			if ( totalFlagged > 0 ) {
				const ids = [ ...new Set( flaggedIds ) ].join( ', #' );
				msg      += ', ' + totalFlagged + ' flagged for manual review → posts: #' + ids;
			}
			addLog( msg, totalFlagged > 0 ? 'warning' : 'success' );
			renderPostDetails( details );
		}

		async function runStep3() {
			setStatus( 'step3', 'running' );
			const res = await ajax( 'acss3to4_step3', {} );
			setStatus( 'step3', 'done' );
			let msg = '✓ bricks_global_classes: ' + ( res.updated_count || 0 ) + ' class names updated';
			if ( res.converted || res.flagged ) {
				msg += ' — ' + ( res.converted || 0 ) + ' value conversions';
				if ( res.flagged ) {
					msg += ', ' + res.flagged + ' flagged';
				}
			}
			addLog( msg, res.flagged ? 'warning' : 'success' );
			renderClassDetails( res.details || [] );
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

		function renderPostDetails( details ) {
			if ( ! Array.isArray( details ) || ! details.length ) {
				return;
			}

			details.forEach( function ( detail ) {
				const sampleText = formatSamples( detail.samples );
				let message = '  Post #' + detail.post_id + ' [' + detail.meta_key + ']: '
					+ ( detail.converted || 0 ) + ' converted';
				if ( detail.flagged ) {
					message += ', ' + detail.flagged + ' flagged';
				}
				if ( sampleText ) {
					message += ' — ' + sampleText;
				}
				addLog( message, detail.flagged ? 'warning' : 'success' );
			} );
		}

		function renderClassDetails( details ) {
			if ( ! Array.isArray( details ) || ! details.length ) {
				return;
			}

			details.forEach( function ( detail ) {
				const sampleText = formatSamples( detail.samples );
				let message = '  Class ' + ( detail.class_name || '(unnamed)' ) + ': ';
				if ( detail.renamed ) {
					message += 'name updated';
				} else {
					message += 'name unchanged';
				}
				if ( detail.converted || detail.flagged ) {
					message += ', ' + ( detail.converted || 0 ) + ' converted';
					if ( detail.flagged ) {
						message += ', ' + detail.flagged + ' flagged';
					}
				}
				if ( sampleText ) {
					message += ' — ' + sampleText;
				}
				addLog( message, detail.flagged ? 'warning' : 'success' );
			} );
		}

		function formatSamples( samples ) {
			if ( ! Array.isArray( samples ) || ! samples.length ) {
				return '';
			}

			return samples.slice( 0, 2 ).map( function ( sample ) {
				return sample.path + ': ' + shorten( sample.before ) + ' -> ' + shorten( sample.after );
			} ).join( ' | ' );
		}

		function shorten( value ) {
			const stringValue = String( value || '' );
			return stringValue.length > 80 ? stringValue.slice( 0, 77 ) + '...' : stringValue;
		}
	} );
}() );
