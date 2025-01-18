{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "0.4.0";

  src = ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-OdvZTs3Pe0QPUYn6AnjlNrb5I4ePKAlsRAlliW6dpwA=";
}
