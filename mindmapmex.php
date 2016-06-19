<?php

// mindmapmex, the mindmap extension for mediawiki
// (c) 2016 by Thorsten Staerk, www.staerk.de/thorsten
// requires dot to be installed and runnable

$wgExtensionFunctions[] = "wfmindmapextension";

function wfmindmapextension()
{
  new mindmapextension();
}

class mindmapextension 
{

  public function __construct()
  {
    global $wgParser;
    $wgParser->setHook('mindmap', array(&$this, 'mindmaphtml'));
  }
  
  function mindmaphtml($code, $argv, $parser)
  {
    // handle arguments like this:
    //$align=htmlentities($argv['align']);
    $lines=explode($code,"\n");
    $dotcodestart='digraph "Wikimap" 
{
  layout=neato
  overlap=scalexy
  node[shape=box, color="#FFB522"; style=rounded; style="rounded,filled"];';
    $dotcodeend='  }';
    $dotcodemiddle=$code;
    $dotcode=$dotcodestart.$dotcodemiddle.$dotcodeend;

    // process $dotcode with the dot command
    $streams = array
    (
      0=>array("pipe","r"),
      1=>array("pipe","w")
    );
    $handle = proc_open('dot -Tsvg',$streams,$pipe);
    if ($handle != false)
    {
      fwrite($pipe[0],$dotcode);
      fclose($pipe[0]);
      $result.=stream_get_contents($pipe[1]);
      fclose($pipe[1]);
      proc_close($handle);
    }
    else 
    {
      $result.="ERR: dot process could not be opened. Install graphviz?";
    }

    $result=preg_replace("/\n/","",$result);
    return $result;
  }
}
