import sys
import yt_dlp
import json

allowed_itags = {'18', '137', '136', '134', '160', '139', '140', '251'}

def get_streams(video_id):
    url = f"https://www.youtube.com/watch?v={video_id}"

    # Set yt-dlp options to use cookies and cache directory
    ydl_opts = {
        'quiet': True,  # Suppress output
        # 'cookiefile': '/home/kirops/myway/backend/cookies.txt',  # Ensure this file exists and is readable
        'cache-dir': '/var/cache/yt-dlp' 
    }

    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info_dict = ydl.extract_info(url, download=False)
            streams = info_dict.get('formats', [])

        stream_list = []
        for stream in streams:

            itag = stream.get('format_id')

            try:
                itag = int(itag)
            except (ValueError, TypeError):
                continue

            if 'url' in stream and str(itag) in allowed_itags:
                resolution = f"{stream.get('height', 'audio only')}p" if stream.get('height') else "audio only"
                # stream_type = f"video/{stream.get('ext')}" if stream.get('vcodec') != 'none' else f"audio/{stream.get('ext')}"
                stream_type = f"video/{stream.get('ext')}" if stream.get('vcodec') != 'none' else f"audio/mp4"

                stream_list.append({
                    'itag': itag,
                    'resolution': resolution,
                    'type': stream_type,
                    'url': stream.get('url')
                })

        return json.dumps({
            'title': info_dict.get('title'),
            'streams': stream_list
        }, indent=4)

    except yt_dlp.utils.DownloadError as e:
        return json.dumps({'error': f'Error downloading video: {str(e)}'})

    except Exception as e:
        return json.dumps({'error': f'An unexpected error occurred: {str(e)}'})

if __name__ == '__main__':
    if len(sys.argv) > 1:
        video_id = sys.argv[1]
        print(get_streams(video_id))
    else:
        print(json.dumps({'error': 'Please provide a video ID'}))
